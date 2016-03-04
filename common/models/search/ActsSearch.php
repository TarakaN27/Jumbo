<?php

namespace common\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Acts;

/**
 * ActsSearch represents the model behind the search form about `common\models\Acts`.
 */
class ActsSearch extends Acts
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
                'id', 'act_num' ,'cuser_id',
                'buser_id', 'service_id', 'template_id',
                'sent', 'change', 'created_at', 'updated_at'
            ], 'integer'],
            [['amount', 'act_date','from_date','to_date'], 'safe'],
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
        $query = Acts::find();
        $query = $this->queryHelper($query,$params);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
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
        $this->load($params);

        $query->andFilterWhere([
            'id' => $this->id,
            'act_num' => $this->act_num,
            'cuser_id' => $this->cuser_id,
            'buser_id' => $this->buser_id,
            'service_id' => $this->service_id,
            'template_id' => $this->template_id,
            'act_date' => $this->act_date,
            'sent' => $this->sent,
            'change' => $this->change,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        if(!empty($this->from_date))
            $query->andWhere(self::tableName().'.act_date >= :dateFrom',[':dateFrom' => date('Y-m-d',strtotime($this->from_date))]);

        if(!empty($this->to_date))
            $query->andWhere(self::tableName().'.act_date <= :dateTo',[':dateTo' => date('Y-m-d',strtotime($this->to_date))]);

        $query->andFilterWhere(['like', 'amount', $this->amount]);


        if(
            !empty($this->cuser_id)||
            !empty($this->buser_id)||
            !empty($this->service_id)||
            !empty($this->template_id)||
            !empty($this->act_date)||
            !empty($this->sent)||
            !empty($this->from_date)||
            !empty($this->to_date)
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
        $query = Acts::find()->select('amount');
        $query = $this->queryHelper($query,$params);

        if(!$this->bCountTotal)
            return NULL;
        $iRes = 0;
        $arAct = $query->all();
        if(empty($arAct))
            return NULL;

        foreach($arAct as $act)
            $iRes+=$act->amount;
        return $iRes;
    }
}
