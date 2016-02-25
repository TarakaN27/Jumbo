<?php

namespace common\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\EnrollmentRequest;

/**
 * EnrollmentRequestSearch represents the model behind the search form about `common\models\EnrollmentRequest`.
 */
class EnrollmentRequestSearch extends EnrollmentRequest
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[
                'id', 'payment_id', 'pr_payment_id',
                'service_id', 'assigned_id', 'cuser_id',
                'pay_currency', 'pay_date', 'created_at', 'updated_at','status'], 'integer'],
            [['amount', 'pay_amount'], 'number'],
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
        $query = EnrollmentRequest::find()->with('cuser','assigned','service');

        if(!empty($additionQuery))
        {
            $query->where($additionQuery);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'defaultPageSize' => Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => [1,1000]
            ],
            'sort'=> [
                'defaultOrder' => [
                    'status'=>SORT_ASC,
                    'created_at' => SORT_DESC
                ]
            ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'payment_id' => $this->payment_id,
            'pr_payment_id' => $this->pr_payment_id,
            'service_id' => $this->service_id,
            'assigned_id' => $this->assigned_id,
            'cuser_id' => $this->cuser_id,
            'amount' => $this->amount,
            'pay_amount' => $this->pay_amount,
            'pay_currency' => $this->pay_currency,
            'pay_date' => $this->pay_date,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'status' => $this->status
        ]);

        return $dataProvider;
    }
}
