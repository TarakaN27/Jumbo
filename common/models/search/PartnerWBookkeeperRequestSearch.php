<?php

namespace common\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\PartnerWBookkeeperRequest;

/**
 * PartnerWBookkeeperRequestSearch represents the model behind the search form about `common\models\PartnerWBookkeeperRequest`.
 */
class PartnerWBookkeeperRequestSearch extends PartnerWBookkeeperRequest
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[
                'id', 'buser_id', 'partner_id',
                'contractor_id', 'currency_id', 'legal_id',
                'request_id', 'created_by', 'status',
                'created_at', 'updated_at'
            ], 'integer'],
            [['amount'], 'number'],
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
        $query = PartnerWBookkeeperRequest::find()->with('buser','partner','contractor','currency','legal');

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
            'buser_id' => $this->buser_id,
            'partner_id' => $this->partner_id,
            'contractor_id' => $this->contractor_id,
            'amount' => $this->amount,
            'currency_id' => $this->currency_id,
            'legal_id' => $this->legal_id,
            'request_id' => $this->request_id,
            'created_by' => $this->created_by,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        return $dataProvider;
    }
}
