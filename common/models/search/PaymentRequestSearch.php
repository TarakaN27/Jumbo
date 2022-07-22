<?php

namespace common\models\search;

use common\models\ExchangeRates;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\PaymentRequest;
use yii\helpers\ArrayHelper;

/**
 * PaymentRequestSearch represents the model behind the search form about `common\models\PaymentRequest`.
 */
class PaymentRequestSearch extends PaymentRequest
{

    public
        $from_date,
        $to_date,
        $managerID;

    protected
        $countTotal = FALSE;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[
                'id', 'cntr_id', 'manager_id',
                'owner_id', 'is_unknown',  'currency_id',
                'legal_id', 'dialog_id', 'status',
                'created_at', 'updated_at'
            ], 'integer'],
            [[
                'pay_date','user_name',
                'description','managerID',
                'from_date','to_date'
            ], 'safe'],
            [['pay_summ'], 'number'],
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
        $query = PaymentRequest::find();

        $query = $this->queryHelper($query,$params,$additionQuery);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'defaultPageSize' => Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => [1,1000]
            ],
            'sort'=> ['defaultOrder' => ['id'=>SORT_DESC]]
        ]);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        return $dataProvider;
    }

    protected function queryHelper($query,$params,$additionQuery = NULL)
    {
        if(!empty($additionQuery))
            $query->where($additionQuery);

        $this->load($params);

        if(!empty($this->managerID)) {
            $query->joinWith('cuser c');
			
			if(is_array($this->managerID)) {
				$this->managerID = implode(",", $this->managerID);
				$query->andWhere('('.PaymentRequest::tableName().'.manager_id IS NULL OR '.PaymentRequest::tableName().'.manager_id IN ( '.$this->managerID.' ) OR c.manager_id IN ( '.$this->managerID.' ) )');
			} else {
				$query->andWhere('('.PaymentRequest::tableName().'.manager_id IS NULL OR '.PaymentRequest::tableName().'.manager_id = :manID OR c.manager_id = :manID )', [':manID' => $this->managerID]);
			}
		}

		if(isset($this->payed)) {
			$query->andWhere(['payed'=>$this->payed]);
		}

        if(!empty($this->pay_date))
            $query->andWhere("FROM_UNIXTIME(pay_date,'%d-%m-%Y') = '".date('d-m-Y',$this->pay_date)."'");

        $query->andFilterWhere([
            'id' => $this->id,
            'cntr_id' => $this->cntr_id,
            'manager_id' => $this->manager_id,
            'owner_id' => $this->owner_id,
            'is_unknown' => $this->is_unknown,
            //'pay_date' => $this->pay_date,
            'pay_summ' => $this->pay_summ,
            'currency_id' => $this->currency_id,
            'legal_id' => $this->legal_id,
            'dialog_id' => $this->dialog_id,
             PaymentRequest::tableName().'.status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);
		
        $query->andFilterWhere(['like', 'user_name', $this->user_name])
            ->andFilterWhere(['like', 'description', $this->description]);

        if(!empty($this->from_date))
            $query->andWhere(" pay_date >= :dateFrom",[':dateFrom' => strtotime($this->from_date.' 00:00:00')]);

        if(!empty($this->to_date))
            $query->andWhere(" pay_date <= :dateTo",[':dateTo' => strtotime($this->to_date.' 23:59:59')]);

        if(
            !empty($this->managerID) ||
            !empty($this->pay_date) ||
            !empty($this->cntr_id) ||
            !empty($this->owner_id) ||
            !empty($this->is_unknown) ||
            !empty($this->pay_summ) ||
            !empty($this->currency_id) ||
            !empty($this->legal_id) ||
            !empty($this->status) ||
            !empty($this->user_name) ||
            !empty($this->from_date) ||
            !empty($this->to_date)
        )
            $this->countTotal = TRUE;

        return $query;
    }

    /**
     * @param $params
     * @param null $additionQuery
     * @return array
     */
    public function totalCount($params,$additionQuery=NULL)
    {
        $query = PaymentRequest::find()->select(['pay_summ','currency_id']);
        $query = $this->queryHelper($query,$params,$additionQuery);
        if(!$this->countTotal)
            return [];
        $arReq = $query->all();
        if(empty($arReq))
            return [];

        $arResTmp = [];
        foreach($arReq as $req)
        {
            if(isset($arResTmp[$req->currency_id]))
                $arResTmp[$req->currency_id]+=$req->pay_summ;
            else
                $arResTmp[$req->currency_id]=$req->pay_summ;
        }

        $arCurrency = ExchangeRates::find()->select(['id','code'])->where(['id' => array_keys($arResTmp)])->all();
        $arCurrency = ArrayHelper::map($arCurrency,'id','code');
        $arResult = [];
        foreach($arResTmp as $key => $value)
        {
            if(isset($arCurrency[$key]))
                $arResult[$arCurrency[$key]] = $value;
            else
                $arResult[$key] = $value;
        }

        return $arResult;
    }
}
