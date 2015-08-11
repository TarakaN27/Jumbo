<?php

namespace common\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\PaymentRequest;

/**
 * PaymentRequestSearch represents the model behind the search form about `common\models\PaymentRequest`.
 */
class PaymentRequestSearch extends PaymentRequest
{

    public
        $managerID;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'cntr_id', 'manager_id', 'owner_id', 'is_unknown',  'currency_id', 'legal_id', 'dialog_id', 'status', 'created_at', 'updated_at'], 'integer'],
            [['pay_date','user_name', 'description','managerID'], 'safe'],
            [['pay_summ'], 'number'],
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
        $query = PaymentRequest::find();

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

        if(!empty($this->managerID))
            $query->andWhere('( manager_id IS NULL OR manager_id = :manID )',[':manID' => $this->managerID]);

        if(!empty($this->pay_date))
            $query->andWhere("FROM_UNIXTIME(pay_date,'%d-%m-%Y') = '".date('d-m-Y',$this->pay_date)."'");

        $query->andFilterWhere([
            'id' => $this->id,
            'cntr_id' => $this->cntr_id,
            'manager_id' => $this->manager_id,
            'owner_id' => $this->owner_id,
            'is_unknown' => $this->is_unknown,
            //'pay_date' => $this->pay_date,
            'pay_summ' => $this->pay_summ,
            'currency_id' => $this->currency_id,
            'legal_id' => $this->legal_id,
            'dialog_id' => $this->dialog_id,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'user_name', $this->user_name])
            ->andFilterWhere(['like', 'description', $this->description]);

        return $dataProvider;
    }
}
