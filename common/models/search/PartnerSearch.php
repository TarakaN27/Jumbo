<?php

namespace common\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Partner;

/**
 * PartnerSearch represents the model behind the search form about `common\models\Partner`.
 */
class PartnerSearch extends Partner
{
    public
        $fio;

    public function afterFind()
    {
        $this->fio = $this->getFio();
        return parent::afterFind();
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'status', 'created_at', 'updated_at'], 'integer'],
            [['fio','fname', 'lname', 'mname', 'description', 'email', 'phone', 'post_address', 'ch_account', 'psk'], 'safe'],
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
        $query = Partner::find();

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
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

				$query->andFilterWhere(['like', 'fname', $this->fname])
					->andFilterWhere(['like', 'lname', $this->lname])
					->andFilterWhere(['like', 'mname', $this->mname])
					->andFilterWhere(['like', 'description', $this->description])
					->andFilterWhere(['like', 'email', $this->email])
					->andFilterWhere(['like', 'phone', $this->phone])
					->andFilterWhere(['like', 'post_address', $this->post_address])
					->andFilterWhere(['like', 'ch_account', $this->ch_account])
					;

        if(!empty($this->fio))
            $query->andWhere(' ( lname LIKE :fio OR
                fname LIKE :fio OR mname LIKE :fio ) ',[':fio' =>'%'.$this->fio.'%' ]);

        return $dataProvider;
    }
}
