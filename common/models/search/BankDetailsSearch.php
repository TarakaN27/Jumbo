<?php

namespace common\models\search;

use common\models\BankDetails;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * LegalPersonSearch represents the model behind the search form about `common\models\LegalPerson`.
 */
class BankDetailsSearch extends BankDetails
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'status', 'legal_person_id','created_at', 'updated_at'], 'integer'],
            [['name', 'bank_details', 'legal_person_id'], 'safe'],
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
        $query = BankDetails::find();

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
            'status' => $this->status,
            'legal_person_id'=> $this->legal_person_id,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'bank_details', $this->bank_details]);

        return $dataProvider;
    }
}
