<?php

namespace common\models\search;

use common\models\CUser;
use common\models\ExchangeRates;
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
        $from_date,
        $to_date;

    protected
        $countTotal = FALSE;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'cuser_id','currency_id', 'service_id', 'legal_id', 'created_at', 'updated_at'], 'integer'],
            [['pay_summ'], 'number'],
            [[
                'pay_date',
                'description',
                'payment_order',
                'from_date',
                'to_date',
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
        $query = Payments::find()->with('legal','service','cuser','currency');

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
            $query->where([CUser::tableName().'.manager_id' => Yii::$app->user->id]);
        }

        $this->load($params);

        if(!empty($this->pay_date))
            $query->andWhere("FROM_UNIXTIME(pay_date,'%d-%m-%Y') = '".date('d-m-Y',$this->pay_date)."'");

        if(!empty($this->from_date))
            $query->andWhere(" pay_date >= :dateFrom",[':dateFrom' => strtotime($this->from_date.' 00:00:00')]);

        if(!empty($this->to_date))
            $query->andWhere(" pay_date <= :dateTo",[':dateTo' => strtotime($this->to_date.' 23:59:59')]);

        $query->andFilterWhere([
            'id' => $this->id,
            'cuser_id' => $this->cuser_id,
            'pay_summ' => $this->pay_summ,
            'currency_id' => $this->currency_id,
            'service_id' => $this->service_id,
            'legal_id' => $this->legal_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
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
            !empty($this->payment_order)
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
        $query = Payments::find()->select(['pay_summ','currency_id']);
        $query = $this->queryHelper($query,$params);
        $arTmp = $query->all();

        if(!$this->countTotal)
            return [];

        if(empty($arTmp))
            return [];
        $arResultTmp = [];
        foreach($arTmp as $tmp)
        {
            if(isset($arResultTmp[$tmp->currency_id]))
                $arResultTmp[$tmp->currency_id]+=$tmp->pay_summ;
            else
                $arResultTmp[$tmp->currency_id]=$tmp->pay_summ;
        }

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
}
