<?php

namespace common\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\BuserInviteCode;

/**
 * BuserInviteCodeSearch represents the model behind the search form about `common\models\BuserInviteCode`.
 */
class BuserInviteCodeSearch extends BuserInviteCode
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'user_type', 'buser_id', 'status',  'updated_at'], 'integer'],
            [['code', 'created_at','email'], 'safe'],
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
        $query = BuserInviteCode::find();

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
            'user_type' => $this->user_type,
            'buser_id' => $this->buser_id,
            'status' => $this->status,
           // 'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        if(!empty($this->created_at))
            $query->andWhere(" FROM_UNIXTIME(created_at,'%d-%m-%Y') = :createdAt ",[':createdAt' => $this->created_at]);

        $query->andFilterWhere(['like', 'code', $this->code])
            ->andFilterWhere(['like', 'email', $this->email]);

        return $dataProvider;
    }
}
