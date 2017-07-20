<?php

namespace common\models\search;

use common\models\EnrollmentRequest;
use common\models\ExchangeCurrencyHistory;
use common\models\ExchangeRates;
use common\models\PaymentCondition;
use common\models\PaymentsCalculations;
use common\models\Services;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Enrolls;
use yii\db\QueryBuilder;
use yii\helpers\ArrayHelper;

/**
 * EnrollsSearch represents the model behind the search form about `common\models\Enrolls`.
 */
class EnrollsSearch extends Enrolls
{

    public
        $from_date,
        $to_date,
        $unitname;

    protected
        $countTotal = false;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'enr_req_id', 'service_id', 'cuser_id', 'buser_id',  'updated_at','enroll_unit_id'], 'integer'],
            [['amount', 'repay', 'enroll'], 'number'],
            [['created_at','description','from_date','to_date','payName','rateName','rate_nbrb'], 'safe'],
            ['unitname','string'],
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
    public function search($params,$additionQuery = [],$addParams = [])
    {
        $query = Enrolls::find()
            ->addSelect(Enrolls::tableName().'.*, '.PaymentCondition::tableName().'.name as payName, '.ExchangeRates::tableName().'.name as rateName, '.ExchangeCurrencyHistory::tableName().'.rate_nbrb')
            ->joinWith('cuser')
            ->joinWith('service serv')
            ->joinWith('enrReq req')
            ->leftJoin(PaymentsCalculations::tableName(),PaymentsCalculations::tableName().'.payment_id = req.payment_id')
            ->leftJoin(PaymentCondition::tableName(),PaymentCondition::tableName().'.id = '.PaymentsCalculations::tableName().'.pay_cond_id')
            ->leftJoin(ExchangeRates::tableName(),ExchangeRates::tableName().'.id = '.PaymentCondition::tableName().'.cond_currency')
            ->leftJoin(ExchangeCurrencyHistory::tableName(),ExchangeCurrencyHistory::tableName().'.currency_id = '.PaymentCondition::tableName().'.cond_currency')
            ->where(ExchangeCurrencyHistory::tableName().".date = DATE_FORMAT(FROM_UNIXTIME(`req`.`pay_date`), '%Y-%m-%d')");

        $query = $this->queryHelper($query,$params,$additionQuery,$addParams);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'defaultPageSize' => Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => [1,1000]
            ],
            'sort'=> [
                'defaultOrder' => [
                    'created_at'=>SORT_DESC
                ]
            ]
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
     * @param array $additionQuery
     * @param array $addParams
     * @return mixed
     */
    protected function queryHelper($query,$params,$additionQuery = [],$addParams = [])
    {

        if(!empty($additionQuery))
            $query->where($additionQuery);

        if(!empty($addParams))
            $query->params($addParams);

        $this->load($params);

        $query->andFilterWhere([
            'id' => $this->id,
            Enrolls::tableName().'.amount' => $this->amount,
            'repay' => $this->repay,
            'enroll' => $this->enroll,
            Enrolls::tableName().'.enr_req_id' => $this->enr_req_id,
            Enrolls::tableName().'.service_id' => $this->service_id,
            'cuser_id' => $this->cuser_id,
            'buser_id' => $this->buser_id,
            //'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'enroll_unit_id' => $this->enroll_unit_id,
        ]);

        if(!empty($this->created_at))
            $query->andWhere("FROM_UNIXTIME(".self::tableName().".created_at,'%d-%m-%Y') = '".date('d-m-Y',strtotime($this->created_at))."'");

        if(!empty($this->from_date))
            $query->andWhere(self::tableName().".created_at >= :dateFrom",[':dateFrom' => strtotime($this->from_date.' 00:00:00')]);

        if(!empty($this->to_date))
            $query->andWhere(self::tableName().".created_at <= :dateTo",[':dateTo' => strtotime($this->to_date.' 23:59:59')]);

        $query->andFilterWhere(['like', 'description', $this->description]);

        $query->andFilterWhere(['like','serv.enroll_unit',$this->unitname]);

        $query->andFilterWhere([PaymentCondition::tableName().'.id'=>$this->payName]);
        $query->andFilterWhere([ExchangeRates::tableName().'.id'=>$this->rateName]);
        $query->andFilterWhere([ExchangeCurrencyHistory::tableName().'.rate_nbrb'=>$this->rate_nbrb]);


        if(
            !empty($this->amount)||
            !empty($this->repay)||
            !empty($this->enroll)||
            !empty($this->enr_req_id)||
            !empty($this->service_id)||
            !empty($this->cuser_id)||
            !empty($this->buser_id)||
            !empty($this->created_at)||
            !empty($this->unitname)||
            !empty($this->from_date)||
            !empty($this->payName)||
            !empty($this->rateName)||
            !empty($this->rate_nbrb)||
            !empty($this->to_date)
        )
            $this->countTotal = TRUE;


        return $query;
    }

    /**
     * @param $params
     * @param array $additionQuery
     * @param array $addParams
     * @return array
     */
    public function totalCount($params,$additionQuery = [],$addParams = [])
    {
        $query = Enrolls::find()->select(['sumAmount'=>'SUM('.Enrolls::tableName().'.amount)', 'unitEnrollName'=>'ue.name', Enrolls::tableName().'.service_id', Enrolls::tableName().'.cuser_id', 'servName'=>'serv.name',Enrolls::tableName().'.enroll_unit_id'])
            ->joinWith('cuser')
            ->joinWith('service serv')
            ->joinWith('unitEnroll ue')
            ->joinWith('enrReq req',false)
            ->leftJoin(PaymentsCalculations::tableName(),PaymentsCalculations::tableName().'.payment_id = req.payment_id')
            ->leftJoin(PaymentCondition::tableName(),PaymentCondition::tableName().'.id = '.PaymentsCalculations::tableName().'.pay_cond_id')
            ->leftJoin(ExchangeRates::tableName(),ExchangeRates::tableName().'.id = '.PaymentCondition::tableName().'.cond_currency')
            ->leftJoin(ExchangeCurrencyHistory::tableName(),ExchangeCurrencyHistory::tableName().'.currency_id = '.PaymentCondition::tableName().'.cond_currency')
            ->where(ExchangeCurrencyHistory::tableName().".date = DATE_FORMAT(FROM_UNIXTIME(`req`.`pay_date`), '%Y-%m-%d')")
            ->groupBy([Enrolls::tableName().'.service_id', Enrolls::tableName().'.enroll_unit_id'])
            ->asArray();

        $query = $this->queryHelper($query,$params,$additionQuery,$addParams);

        if(!$this->countTotal)
            return [];
        $arEnroll = $query->all();

        if(empty($arEnroll))
            return [];


        foreach($arEnroll as $enroll)
            $arResult[$enroll['service_id'].'-'.$enroll['enroll_unit_id']]=['amount'=>$enroll['sumAmount'], 'nameServiceWithUnitEnroll' => $enroll['servName'].'['.$enroll['unitEnrollName'].']'];
        return $arResult;
    }
}
