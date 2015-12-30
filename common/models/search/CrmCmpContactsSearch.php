<?php

namespace common\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\CrmCmpContacts;

/**
 * CrmCmpContactsSearch represents the model behind the search form about `common\models\CrmCmpContacts`.
 */
class CrmCmpContactsSearch extends CrmCmpContacts
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'cmp_id', 'type', 'assigned_at', 'created_at', 'updated_at','is_opened'], 'integer'],
            [['fio', 'post', 'description', 'addition_info', 'phone', 'email'], 'safe'],
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
    public function search($params,$addCondition = NULL,$arParams = [])
    {
        $query = CrmCmpContacts::find()->with('assignedAt','cmp');
        //дополнительные условия
        if(!is_null($addCondition))
            $query->where($addCondition,$arParams);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'defaultPageSize' => Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => [1,1000]
            ],
            'sort'=> ['defaultOrder' => ['created_at'=>SORT_DESC]]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'cmp_id' => $this->cmp_id,
            'type' => $this->type,
            'assigned_at' => $this->assigned_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'is_opened' => $this->is_opened
        ]);

        $query->andFilterWhere(['like', 'fio', $this->fio])
            ->andFilterWhere(['like', 'post', $this->post])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'addition_info', $this->addition_info])
            ->andFilterWhere(['like', 'phone', $this->phone])
            ->andFilterWhere(['like', 'email', $this->email]);

        return $dataProvider;
    }
}
