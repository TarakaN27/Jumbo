<?php

namespace common\models\search;

use common\models\ExchangeRates;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Expense;

/**
 * ExpenseSearch represents the model behind the search form about `common\models\Expense`.
 */
class ExpenseSearch extends Expense
{

    public
        $from_date,
        $to_date;

    protected
        $bCountTotal = FALSE;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id',  'currency_id', 'legal_id', 'cuser_id', 'cat_id', 'created_at', 'updated_at'], 'integer'],
            [['pay_summ'], 'number'],
            [['pay_date','description','from_date','to_date'], 'safe'],
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
    public function search($params,$additionQuery = NULL)
    {
        $query = Expense::find()->with('currency','legal','cat','cuser');

        $query->joinWith('legal');

        $query = $this->queryHelper($query,$params,$additionQuery);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'defaultPageSize' => Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => [1,1000]
            ],
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
     * @param null $additionQuery
     * @return mixed
     */
    protected function queryHelper($query,$params,$additionQuery=NULL)
    {
        if($additionQuery)
            $query->where($additionQuery);

        $this->load($params);

        if(!empty($this->pay_date))
            $query->andWhere("FROM_UNIXTIME(pay_date,'%d-%m-%Y') = '".date('d-m-Y',$this->pay_date)."'");

        $query->andFilterWhere([
            'id' => $this->id,
            //'pay_date' => $this->pay_date,
            'pay_summ' => $this->pay_summ,
            'currency_id' => $this->currency_id,
            'legal_id' => $this->legal_id,
            'cuser_id' => $this->cuser_id,
            'cat_id' => $this->cat_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'description', $this->description]);

        if(!empty($this->from_date))
            $query->andWhere(self::tableName().".created_at >= :dateFrom",[':dateFrom' => strtotime($this->from_date.' 00:00:00')]);

        if(!empty($this->to_date))
            $query->andWhere(self::tableName().".created_at <= :dateTo",[':dateTo' => strtotime($this->to_date.' 23:59:59')]);

        if(
            !empty($this->pay_date)||
            !empty($this->pay_summ)||
            !empty($this->currency_id)||
            !empty($this->legal_id)||
            !empty($this->cuser_id)||
            !empty($this->cat_id)||
            !empty($this->created_at)||
            !empty($this->from_date)||
            !empty($this->to_date)
        )
            $this->bCountTotal = TRUE;

        return $query;
    }

    /**
     * @param $params
     * @param null $additionQuery
     * @return array
     */
    public function totalCount($params,$additionQuery=NULL)
    {
        $query = Expense::find()->select(['pay_summ','currency_id',ExchangeRates::tableName().'.code']);
        $query->joinWith('currency');
        $query = $this->queryHelper($query,$params,$additionQuery=NULL);

        if(!$this->bCountTotal)
            return [];

        $arExp = $query->all();

        if(empty($arExp))
            return [];

        $arResult = [];
        foreach($arExp as $exp)
        {
            $name = is_object($exp->currency) ? $exp->currency->code : $exp->currency_id;
            if(isset($arResult[$name]))
                $arResult[$name]+=$exp->pay_summ;
            else
                $arResult[$name] = $exp->pay_summ;
        }

        return $arResult;
    }
}
