<?php

namespace common\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\PartnerCondition;

/**
 * PartnerConditionSearch represents the model behind the search form about `common\models\PartnerCondition`.
 */
class PartnerConditionSearch extends PartnerCondition
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'min_amount', 'max_amount', 'created_at', 'updated_at'], 'integer'],
            [['percent'], 'number'],
            [['start_date'], 'safe'],
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
        $query = PartnerCondition::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'min_amount' => $this->min_amount,
            'max_amount' => $this->max_amount,
            'percent' => $this->percent,
            'start_date' => $this->start_date,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        return $dataProvider;
    }
}
