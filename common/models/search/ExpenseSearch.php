<?php

namespace common\models\search;

use common\models\ExchangeCurrencyHistory;
use common\models\ExchangeRates;
use common\models\ExpenseCategories;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Expense;
use yii\web\NotFoundHttpException;

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
        $query->joinWith('cat');
        $query = $this->queryHelper($query,$params,$additionQuery);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'defaultPageSize' => Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => [1,1000]
            ],
            'sort'=> [
                'defaultOrder' => ['id'=>SORT_DESC]
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

        $table = self::tableName();
        $cats = $this->cat_id;
        if(!empty($this->cat_id))
        {
            $exCat = ExpenseCategories::find()->select(['id'])->where(['parent_id' => $this->cat_id])->all();
            foreach($exCat as $c)
                $cats[] = $c->id;
        }else{
            $cats = $this->cat_id;
        }

        $query->andFilterWhere([
            $table.'.id' => $this->id,
            //'pay_date' => $this->pay_date,
            $table.'.pay_summ' => $this->pay_summ,
            $table.'.currency_id' => $this->currency_id,
            $table.'.legal_id' => $this->legal_id,
            $table.'.cuser_id' => $this->cuser_id,
            $table.'.cat_id' => $cats,
            $table.'.created_at' => $this->created_at,
            $table.'.updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', $table.'.description', $this->description]);

        if(!empty($this->from_date))
            $query->andWhere($table.".pay_date >= :dateFrom",[':dateFrom' => strtotime($this->from_date.' 00:00:00')]);

        if(!empty($this->to_date))
            $query->andWhere($table.".pay_date <= :dateTo",[':dateTo' => strtotime($this->to_date.' 23:59:59')]);

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
        $query = Expense::find()->select([
            'pay_date',
            'pay_summ',
            'currency_id',
            ExchangeRates::tableName().'.code',
            'cat_id',
            ExpenseCategories::tableName().'.ignore_at_report',
        ]);
        $query->joinWith('currency');
        $query->joinWith('legal');
        $query->joinWith('cat');
        $query = $this->queryHelper($query,$params,$additionQuery);

        if(!$this->bCountTotal)
            return [];

        $arExp = $query->all();

        if(empty($arExp))
            return [];

        $arResult = [
            'total' => [],
            'totalByr' => 0,
            'withoutIgnore' =>[],
            'totalWithoutIgnore' => 0
        ];
        $arDateHist = [];
        /** @var Expense $exp */
        foreach($arExp as $exp)
        {
            $date = date('Y-m-d',$exp->pay_date);
            if(isset($arDateHist[$date][$exp->currency_id]))
            {
                $curr = $arDateHist[$date][$exp->currency_id];
            }else{
                $curr = ExchangeCurrencyHistory::getCurrencyInBURForDate($exp->pay_date,$exp->currency_id);
                if(!$curr)
                    throw new NotFoundHttpException('Currency not found');
                $arDateHist[$date][$exp->currency_id] = $curr;
            }

            $name = is_object($exp->currency) ? $exp->currency->code : $exp->currency_id;
            if(isset($arResult['total'][$name]))
                $arResult['total'][$name]+=$exp->pay_summ;
            else
                $arResult['total'][$name] = $exp->pay_summ;
            
            $arResult['totalByr']+=(float)$exp->pay_summ*(float)$curr;

            if($exp->cat->ignore_at_report != self::YES)
            {
                if(isset($arResult['withoutIgnore'][$name]))
                    $arResult['withoutIgnore'][$name]+=$exp->pay_summ;
                else
                    $arResult['withoutIgnore'][$name] = $exp->pay_summ;

                $arResult['totalWithoutIgnore']+=(float)$exp->pay_summ*(float)$curr;
            }
        }

        return $arResult;
    }

    /**
     * @param $params
     * @param null $additionQuery
     * @return array
     */
    public function totalCountWithoutIgnore($params,$additionQuery=NULL)
    {
        $query = Expense::find()->select(['pay_summ','currency_id',ExchangeRates::tableName().'.code']);
        $query->joinWith('currency');
        $query->joinWith('category');
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
