<?php

namespace common\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\PaymentCondition;

/**
 * PaymentConditionSearch represents the model behind the search form about `common\models\PaymentCondition`.
 */
class PaymentConditionSearch extends PaymentCondition
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'service_id', 'l_person_id', 'is_resident', 'created_at', 'updated_at','type'], 'integer'],
            [['name', 'description'], 'safe'],
            [['summ_from', 'summ_to', 'corr_factor', 'commission', 'sale', 'tax'], 'number'],
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
        $query = PaymentCondition::find()->with('currency','service','lPerson');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'defaultPageSize' => Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => [1,1000]
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'service_id' => $this->service_id,
            'l_person_id' => $this->l_person_id,
            'is_resident' => $this->is_resident,
            'summ_from' => $this->summ_from,
            'summ_to' => $this->summ_to,
            'corr_factor' => $this->corr_factor,
            'commission' => $this->commission,
            'sale' => $this->sale,
            'tax' => $this->tax,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'type' => $this->type
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'description', $this->description]);

        return $dataProvider;
    }
}
