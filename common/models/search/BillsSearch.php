<?php

namespace common\models\search;

use common\models\CUser;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Bills;

/**
 * BillsSearch represents the model behind the search form about `common\models\Bills`.
 */
class BillsSearch extends Bills
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'manager_id', 'cuser_id', 'l_person_id', 'service_id', 'docx_tmpl_id', 'amount', 'bill_number', 'bill_template', 'use_vat', 'created_at', 'updated_at'], 'integer'],
            [['bill_date', 'description', 'object_text', 'buy_target'], 'safe'],
            [['vat_rate'], 'number'],
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
        $query = Bills::find()->with('service','cuser','lPerson','docxTmpl');

        if(Yii::$app->user->can('only_manager'))
        {
            $query->joinWith('cuser');
            $query->where([CUser::tableName().'.manager_id' => Yii::$app->user->id]);
        }

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
            'manager_id' => $this->manager_id,
            'cuser_id' => $this->cuser_id,
            'l_person_id' => $this->l_person_id,
            'service_id' => $this->service_id,
            'docx_tmpl_id' => $this->docx_tmpl_id,
            'amount' => $this->amount,
            'bill_number' => $this->bill_number,
            //'bill_date' => $this->bill_date,
            'bill_template' => $this->bill_template,
            'use_vat' => $this->use_vat,
            'vat_rate' => $this->vat_rate,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        if(!empty($this->bill_date))
            $query->andFilterWhere([
                'bill_date' => date('Y-m-d',strtotime($this->bill_date))
            ]);
        
        $query->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'object_text', $this->object_text])
            ->andFilterWhere(['like', 'buy_target', $this->buy_target]);

        return $dataProvider;
    }
}
