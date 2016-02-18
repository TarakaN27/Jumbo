<?php

namespace common\models\search;

use common\models\CUser;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Payments;

/**
 * PaymentsSearch represents the model behind the search form about `common\models\Payments`.
 */
class PaymentsSearch extends Payments
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'cuser_id','currency_id', 'service_id', 'legal_id', 'created_at', 'updated_at'], 'integer'],
            [['pay_summ'], 'number'],
            [['pay_date','description','payment_order'], 'safe'],
            [['pay_date'], 'default', 'value' => null],
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
        $query = Payments::find()->with('legal','service','cuser','currency');


        if(Yii::$app->user->can('only_manager'))
        {
            $query->joinWith('cuser');
            $query->where([CUser::tableName().'.manager_id' => Yii::$app->user->id]);
        }


        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> [
                'defaultOrder' => ['id'=>SORT_DESC]
            ],
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

        if(!empty($this->pay_date))
            $query->andWhere("FROM_UNIXTIME(pay_date,'%d-%m-%Y') = '".date('d-m-Y',$this->pay_date)."'");

        $query->andFilterWhere([
            'id' => $this->id,
            'cuser_id' => $this->cuser_id,
            //"FROM_UNIXTIME(pay_date,'%d-%m-%Y')" => $this->pay_date,
            'pay_summ' => $this->pay_summ,
            'currency_id' => $this->currency_id,
            'service_id' => $this->service_id,
            'legal_id' => $this->legal_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

        ]);

        $query->andFilterWhere(['like', 'description', $this->description]);
        $query->andFilterWhere(['like','payment_order',$this->payment_order]);

        return $dataProvider;
    }
}
