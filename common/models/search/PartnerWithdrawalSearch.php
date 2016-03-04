<?php

namespace common\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\PartnerWithdrawal;

/**
 * PartnerWithdrawalSearch represents the model behind the search form about `common\models\PartnerWithdrawal`.
 */
class PartnerWithdrawalSearch extends PartnerWithdrawal
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
            [['id', 'partner_id', 'type', 'created_at', 'updated_at'], 'integer'],
            [['amount'], 'number'],
            [['description','from_date','to_date'], 'safe'],
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
        $query = PartnerWithdrawal::find();

        $query = $this->queryHelper($query,$params);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
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
     */
    protected function queryHelper($query,$params)
    {
        $this->load($params);

        $query->andFilterWhere([
            'id' => $this->id,
            'partner_id' => $this->partner_id,
            'amount' => $this->amount,
            'type' => $this->type,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'description', $this->description]);

        if(!empty($this->from_date))
            $query->andWhere(self::tableName().".created_at >= :dateFrom",[':dateFrom' => strtotime($this->from_date.' 00:00:00')]);

        if(!empty($this->to_date))
            $query->andWhere(self::tableName().".created_at <= :dateTo",[':dateTo' => strtotime($this->to_date.' 23:59:59')]);

        if(
            !empty($this->partner_id)||
            !empty($this->amount)||
            !empty($this->type)||
            !empty($this->from_date)||
            !empty($this->to_date)||
            !empty($this->created_at)
        )
            $this->bCountTotal = TRUE;


        return $query;
    }

    /**
     * @param $params
     * @return int|null
     */
    public function countTotal($params)
    {
        $query = PartnerWithdrawal::find()->select(['amount']);

        $query = $this->queryHelper($query,$params);
        if(!$this->bCountTotal)
            return NULL;

        $arPW = $query->all();
        if(empty($arPW))
            return NULL;

        $iRes = 0;
        foreach($arPW as $pw)
            $iRes+=$pw->amount;

        return $iRes;
    }


}
