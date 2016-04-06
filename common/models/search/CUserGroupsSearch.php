<?php

namespace common\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\CUserGroups;

/**
 * CUserGroupsSearch represents the model behind the search form about `common\models\CUserGroups`.
 */
class CUserGroupsSearch extends CUserGroups
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'created_at', 'updated_at'], 'integer'],
            [['name'], 'safe'],
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
    public function search($params,$addQuery = NULL,$addParams = [])
    {
        $query = CUserGroups::find()
            ->alias('gr')
            ->joinWith('cuserObjects cu');

        if(!empty($addQuery))
            $query->where($addQuery);

        if(!empty($addParams))
            $query->params($addParams);

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
            'gr.id' => $this->id,
            'gr.created_at' => $this->created_at,
            'gr.updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'gr.name', $this->name]);

        return $dataProvider;
    }
}
