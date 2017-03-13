<?php

namespace common\models\search;

use backend\models\BUser;
use common\models\CUser;
use common\models\ExchangeRates;
use common\models\LegalPerson;
use common\models\PaymentRequest;
use common\models\PaymentsSale;
use common\models\Services;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Payments;
use yii\helpers\ArrayHelper;

/**
 * PaymentsSearch represents the model behind the search form about `common\models\Payments`.
 */
class PaymentsSearch extends Payments
{
    public
        $manager,
        $from_date,
        $bank_id,
        $to_date;

    protected
        $countTotal = FALSE;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[
                'id', 'cuser_id','currency_id',
                'service_id', 'legal_id', 'created_at',
                'updated_at','manager','act_close', 'bank_id'
            ], 'integer'],
            [['pay_summ'], 'number'],
            [[
                'pay_date',
                'description',
                'payment_order',
                'from_date',
                'to_date',
                'manager'
            ], 'safe'],
            [['pay_date'], 'default', 'value' => null],
            [['from_date','to_date'],'date','format' => 'php:m.d.Y']
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Payments::find()
            ->select([
                Payments::tableName().'.id',
                'cuser_id',
                Payments::tableName().'.service_id',
                Payments::tableName().'.prequest_id',
                Payments::tableName().'.legal_id',
                Payments::tableName().'.currency_id',
                Payments::tableName().'.pay_date',
                Payments::tableName().'.payment_order',
                Payments::tableName().'.pay_summ',
                'act_close',
                CUser::tableName().'.requisites_id',
                CUser::tableName().'.manager_id',
                Services::tableName().'.name',
                LegalPerson::tableName().'.name',
                ExchangeRates::tableName().'.name',
                BUser::tableName().'.fname',
                BUser::tableName().'.mname',
                BUser::tableName().'.lname',
            ])
            ->joinWith('legal')
            ->joinWith('service')
            ->joinWith('cuser.manager')
            ->joinWith('currency')
            ->joinWith('payRequest');

        $query = $this->queryHelper($query,$params);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> [
                'defaultOrder' => ['id'=>SORT_DESC]
            ],
            'pagination' => [
                'defaultPageSize' => Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => [1,1000]
            ],
        ]);
        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        return $dataProvider;
    }

    /**
     * @param $query
     * @param $params
     * @return mixed
     */
    protected function queryHelper($query,$params)
    {
        if(Yii::$app->user->can('only_manager'))
        {
            $query->joinWith('cuser');
            $cuserIdSales = CUser::find()->select(['id'])->where(['sale_manager_id'=>Yii::$app->user->id])->asArray()->all();
            if($cuserIdSales){
                $cuserIdSales = ArrayHelper::getColumn($cuserIdSales, 'id');
                $query->andWhere(['or', [CUser::tableName().'.manager_id' => Yii::$app->user->id], [PaymentRequest::tableName().'.manager_id' => Yii::$app->user->id], [static::tableName().'.cuser_id'=>$cuserIdSales]]);
            }else
                $query->where([CUser::tableName().'.manager_id' => Yii::$app->user->id]);
        }

        $query->joinWith('cuser');


        $this->load($params);

        if(!empty($this->pay_date))
            $query->andWhere("FROM_UNIXTIME(pay_date,'%d-%m-%Y') = '".date('d-m-Y',$this->pay_date)."'");

        if(!empty($this->from_date))
            $query->andWhere(Payments::tableName().".pay_date >= :dateFrom",[':dateFrom' => strtotime($this->from_date.' 00:00:00')]);

        if(!empty($this->to_date))
            $query->andWhere(Payments::tableName().".pay_date <= :dateTo",[':dateTo' => strtotime($this->to_date.' 23:59:59')]);

        $query->andFilterWhere([
            'id' => $this->id,
            'cuser_id' => $this->cuser_id,
             static::tableName().'.pay_summ' => $this->pay_summ,
            'currency_id' => $this->currency_id,
             static::tableName().'.service_id' => $this->service_id,
             static::tableName().'.legal_id' => $this->legal_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'act_close' => $this->act_close,
            PaymentRequest::tableName().'.bank_id' => $this->bank_id,
            CUser::tableName().'.manager_id' => $this->manager
        ]);

        $query->andFilterWhere(['like', 'description', $this->description]);
        $query->andFilterWhere(['like','payment_order',$this->payment_order]);



        //для работы тотала
        if(
            !empty($this->pay_date) ||
            !empty($this->from_date) ||
            !empty($this->to_date)||
            !empty($this->cuser_id)||
            !empty($this->pay_summ)||
            !empty($this->service_id)||
            !empty($this->legal_id) ||
            !empty($this->currency_id) ||
            !empty($this->payment_order) ||
            !empty($this->manager) ||
            !empty($this->act_close)
        )
            $this->countTotal = TRUE;

        return $query;
    }

    /**
     * @param $params
     * @return array
     */
    public function totalCount($params)
    {
        if(empty($params))
            return [];
        $query = Payments::find()->select(['pay_summ'=>'SUM('.static::tableName().'.pay_summ)',static::tableName().'.currency_id', static::tableName().'.prequest_id'])->groupBy([static::tableName().'.currency_id'])->joinWith('payRequest');

        $query = $this->queryHelper($query,$params);

        $arTmp = $query->all();

        if(!$this->countTotal)
            return [];

        if(empty($arTmp))
            return [];


        $arResultTmp = ArrayHelper::map($arTmp, 'currency_id', 'pay_summ');
        $arCurrency = ExchangeRates::find()->select(['id','code'])->where(['id' => array_keys($arResultTmp)])->all();
        $arCurrency = ArrayHelper::map($arCurrency,'id','code');
        $arResult = [];
        foreach($arResultTmp as $key => $value)
        {
            if(isset($arCurrency[$key]))
                $arResult[$arCurrency[$key]] = $value;
            else
                $arResult[$key] = $value;
        }

        return $arResult;
    }

    public function attributeLabels()
    {
        $arParent = parent::attributeLabels();
        return ArrayHelper::merge($arParent,[
            'manager' => Yii::t('app/book','Responsibility')
        ]);
    }
}
