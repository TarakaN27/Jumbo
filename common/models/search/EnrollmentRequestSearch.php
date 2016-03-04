<?php

namespace common\models\search;

use common\models\Services;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\EnrollmentRequest;

/**
 * EnrollmentRequestSearch represents the model behind the search form about `common\models\EnrollmentRequest`.
 */
class EnrollmentRequestSearch extends EnrollmentRequest
{
    public
        $from_date,
        $to_date;

    protected
        $bCountTotal = FALSE;


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[
                'id', 'payment_id', 'pr_payment_id',
                'service_id', 'assigned_id', 'cuser_id',
                'pay_currency', 'pay_date', 'created_at', 'updated_at','status'], 'integer'],
            [['amount', 'pay_amount'], 'number'],
            [['from_date','to_date'],'safe'],
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
    public function search($params,$additionQuery = NULL)
    {
        $query = EnrollmentRequest::find()->with('cuser','assigned','service');

        $query = $this->queryHelper($query,$params,$additionQuery);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'defaultPageSize' => Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => [1,1000]
            ],
            'sort'=> [
                'defaultOrder' => [
                    'status'=>SORT_ASC,
                    'created_at' => SORT_DESC
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
     * @param null $additionQuery
     * @return mixed
     */
    protected function queryHelper($query,$params,$additionQuery = NULL)
    {
        if(!empty($additionQuery))
        {
            $query->where($additionQuery);
        }

        $this->load($params);

        $query->andFilterWhere([
            'id' => $this->id,
            'payment_id' => $this->payment_id,
            'pr_payment_id' => $this->pr_payment_id,
            'service_id' => $this->service_id,
            'assigned_id' => $this->assigned_id,
            'cuser_id' => $this->cuser_id,
            'amount' => $this->amount,
            'pay_amount' => $this->pay_amount,
            'pay_currency' => $this->pay_currency,
            'pay_date' => $this->pay_date,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            EnrollmentRequest::tableName().'.status' => $this->status
        ]);


        if(!empty($this->from_date))
            $query->andWhere(self::tableName().".created_at >= :dateFrom",[':dateFrom' => strtotime($this->from_date.' 00:00:00')]);

        if(!empty($this->to_date))
            $query->andWhere(self::tableName().".created_at <= :dateTo",[':dateTo' => strtotime($this->to_date.' 23:59:59')]);


        if(
            !empty($this->payment_id)||
            !empty($this->pr_payment_id)||
            !empty($this->service_id)||
            !empty($this->assigned_id)||
            !empty($this->cuser_id)||
            !empty($this->amount)||
            !empty($this->pay_amount)||
            !empty($this->pay_currency)||
            !empty($this->pay_date)||
            !empty($this->created_at)||
            !empty($this->from_date)||
            !empty($this->to_date)
        )
            $this->bCountTotal = TRUE;

        return $query;
    }

    /**
     * @param $params
     * @param null $additionQuery
     * @return array
     */
    public function countTotal($params,$additionQuery = NULL)
    {
        $query = EnrollmentRequest::find()->select([
            'amount',
            'service_id',
            Services::tableName().'.name',
            Services::tableName().'.enroll_unit'
        ]);
        $query->joinWith('service');
        $query = $this->queryHelper($query,$params,$additionQuery);
        if(!$this->bCountTotal)
            return [];

        $arEnrTmp = $query->all();
        $arResult = [];
        foreach($arEnrTmp as $tmp)
        {
            $name = is_object($tmp->service) ? $tmp->service->getNameWithEnrollUnit() : $tmp->service_id;
            if(isset($arResult[$name]))
                $arResult[$name]+=$tmp->amount;
            else
                $arResult[$name]=$tmp->amount;
        }
        return $arResult;
    }
}
