<?php

namespace common\models\search;

use common\models\Services;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\PromisedPayment;

/**
 * PromisedPaymentSearch represents the model behind the search form about `common\models\PromisedPayment`.
 */
class PromisedPaymentSearch extends PromisedPayment
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
                'id', 'cuser_id', 'buser_id_p',
                'service_id' ,'paid_date', 'paid',
                'created_at', 'updated_at'
            ], 'integer'],
            [['amount','from_date','to_date'], 'safe'],
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
        $query = PromisedPayment::find()->with('service');
        $query = $this->queryHelper($query,$params);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere(['like', 'amount', $this->amount]);

        return $dataProvider;
    }

    /**
     * @param $query
     * @param $params
     * @return mixed
     */
    protected function queryHelper($query,$params)
    {
        $this->load($params);

        $query->andFilterWhere([
            'id' => $this->id,
            'cuser_id' => $this->cuser_id,
            'buser_id_p' => $this->buser_id_p,
            'service_id' => $this->service_id,
            'paid_date' => $this->paid_date,
            'paid' => $this->paid,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        if(!empty($this->from_date))
            $query->andWhere(self::tableName().".created_at >= :dateFrom",[':dateFrom' => strtotime($this->from_date.' 00:00:00')]);

        if(!empty($this->to_date))
            $query->andWhere(self::tableName().".created_at <= :dateTo",[':dateTo' => strtotime($this->to_date.' 23:59:59')]);

        if(
            !empty($this->buser_id_p)||
            !empty($this->service_id)||
            !empty($this->cuser_id)||
            !empty($this->paid)||
            !empty($this->paid_date)||
            !empty($this->created_at)||
            !empty($this->from_date)||
            !empty($this->to_date)
        )
            $this->bCountTotal = TRUE;

        return $query;
    }

    /**
     * @param $params
     * @return array
     */
    public function countTotal($params)
    {
        $query = PromisedPayment::find()->select(['amount','service_id',Services::tableName().'.name',Services::tableName().'.enroll_unit']);
        $query->joinWith('service');
        $query = $this->queryHelper($query,$params);
        if(!$this->bCountTotal)
            return [];

        $arPP = $query->all();
        if(empty($arPP))
            return [];

        $arResult = [];
        foreach($arPP as $tmp)
        {
            $name = is_object($tmp->service) ? $tmp->service->getNameWithEnrollUnit() : $tmp->service_id;
            if(isset($arResult[$name]))
                $arResult[$name]+=(float)$tmp->amount;
            else
                $arResult[$name]=(float)$tmp->amount;
        }
        return $arResult;
    }


}
