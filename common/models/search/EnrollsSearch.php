<?php

namespace common\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Enrolls;

/**
 * EnrollsSearch represents the model behind the search form about `common\models\Enrolls`.
 */
class EnrollsSearch extends Enrolls
{

    public
        $unitname;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'enr_req_id', 'service_id', 'cuser_id', 'buser_id',  'updated_at'], 'integer'],
            [['amount', 'repay', 'enroll'], 'number'],
            [['created_at','description'], 'safe'],
            ['unitname','string']
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
    public function search($params,$additionQuery = [],$addParams = [])
    {
        $query = Enrolls::find();
        $query->joinWith('cuser');
        $query->joinWith('service serv');

        if(!empty($additionQuery))
            $query->where($addParams);

        if(!empty($addParams))
            $query->params($addParams);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'defaultPageSize' => Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => [1,1000]
            ],
            'sort'=> [
                'defaultOrder' => [
                    'created_at'=>SORT_DESC
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
            'amount' => $this->amount,
            'repay' => $this->repay,
            'enroll' => $this->enroll,
            'enr_req_id' => $this->enr_req_id,
            'service_id' => $this->service_id,
            'cuser_id' => $this->cuser_id,
            'buser_id' => $this->buser_id,
            //'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        if(!empty($this->created_at))
            $query->andWhere("FROM_UNIXTIME(".self::tableName().".created_at,'%d-%m-%Y') = '".date('d-m-Y',strtotime($this->created_at))."'");

        $query->andFilterWhere(['like', 'description', $this->description]);

        $query->andFilterWhere(['like','serv.enroll_unit',$this->unitname]);

        return $dataProvider;
    }
}
