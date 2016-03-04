<?php

namespace common\models\search;

use common\models\Services;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Enrolls;
use yii\helpers\ArrayHelper;

/**
 * EnrollsSearch represents the model behind the search form about `common\models\Enrolls`.
 */
class EnrollsSearch extends Enrolls
{

    public
        $from_date,
        $to_date,
        $unitname;

    protected
        $countTotal = false;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'enr_req_id', 'service_id', 'cuser_id', 'buser_id',  'updated_at'], 'integer'],
            [['amount', 'repay', 'enroll'], 'number'],
            [['created_at','description','from_date','to_date'], 'safe'],
            ['unitname','string'],
            [['from_date','to_date'],'date','format' => 'php:m.d.Y']
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
        $query = $this->queryHelper($query,$params,$additionQuery,$addParams);
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

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        return $dataProvider;
    }

    /**
     * @param $query
     * @param $params
     * @param array $additionQuery
     * @param array $addParams
     * @return mixed
     */
    protected function queryHelper($query,$params,$additionQuery = [],$addParams = [])
    {

        if(!empty($additionQuery))
            $query->where($addParams);

        if(!empty($addParams))
            $query->params($addParams);

        $this->load($params);

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

        if(!empty($this->from_date))
            $query->andWhere(self::tableName().".created_at >= :dateFrom",[':dateFrom' => strtotime($this->from_date.' 00:00:00')]);

        if(!empty($this->to_date))
            $query->andWhere(self::tableName().".created_at <= :dateTo",[':dateTo' => strtotime($this->to_date.' 23:59:59')]);

        $query->andFilterWhere(['like', 'description', $this->description]);

        $query->andFilterWhere(['like','serv.enroll_unit',$this->unitname]);


        if(
            !empty($this->amount)||
            !empty($this->repay)||
            !empty($this->enroll)||
            !empty($this->enr_req_id)||
            !empty($this->service_id)||
            !empty($this->cuser_id)||
            !empty($this->buser_id)||
            !empty($this->created_at)||
            !empty($this->unitname)||
            !empty($this->from_date)||
            !empty($this->to_date)
        )
            $this->countTotal = TRUE;


        return $query;
    }

    /**
     * @param $params
     * @param array $additionQuery
     * @param array $addParams
     * @return array
     */
    public function totalCount($params,$additionQuery = [],$addParams = [])
    {
        $query = Enrolls::find()->select(['amount','service_id']);
        $query = $this->queryHelper($query,$params,$additionQuery,$addParams);
        if(!$this->countTotal)
            return [];
        $arEnrTmp = $query->all();
        if(empty($arEnrTmp))
            return [];

        $arResultTmp = [];
        foreach($arEnrTmp as $tmp)
        {
            if(isset($arResultTmp[$tmp->service_id]))
                $arResultTmp[$tmp->service_id]+=$tmp['amount'];
            else
                $arResultTmp[$tmp->service_id]=$tmp['amount'];
        }

        $arServ = ArrayHelper::map(
            Services::find()->select(['id','name','enroll_unit'])->where(['id' => array_keys($arResultTmp)])->all(),
            'id','nameWithEnrollUnit'
        );

        $arResult = [];
        foreach($arResultTmp as $key => $value)
        {
            if(isset($arServ[$key]))
                $arResult[$arServ[$key]] = $value;
            else
                $arResult[$key] = $value;
        }

        return $arResult;
    }
}
