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
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'cuser_id', 'buser_id', 'service_id', 'template_id', 'sent', 'change', 'created_at', 'updated_at'], 'integer'],
            [['amount', 'act_date'], 'safe'],
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

        $query->andFilterWhere(['like', 'amount', $this->amount]);

        return $dataProvider;
    }
}
