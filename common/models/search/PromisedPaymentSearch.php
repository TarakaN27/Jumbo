<?php

namespace common\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\PromisedPayment;

/**
 * PromisedPaymentSearch represents the model behind the search form about `common\models\PromisedPayment`.
 */
class PromisedPaymentSearch extends PromisedPayment
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'cuser_id', 'buser_id_p','service_id' ,'paid_date', 'paid', 'created_at', 'updated_at'], 'integer'],
            [['amount'], 'safe'],
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
            'buser_id_p' => $this->buser_id_p,
            'service_id' => $this->service_id,
            'paid_date' => $this->paid_date,
            'paid' => $this->paid,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'amount', $this->amount]);

        return $dataProvider;
    }
}
