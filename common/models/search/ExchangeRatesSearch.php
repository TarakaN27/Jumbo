<?php

namespace common\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\ExchangeRates;

/**
 * ExchangeRatesSearch represents the model behind the search form about `common\models\ExchangeRates`.
 */
class ExchangeRatesSearch extends ExchangeRates
{
    /**
     * @inheritdoc
     */
    public $nbrb_rate_old;
    public function rules()
    {
        return [
            [['id', 'nbrb', 'cbr', 'created_at', 'updated_at','show_at_widget'], 'integer'],
            [['name', 'code'], 'safe'],
            [['nbrb_rate', 'nbrb_rate_old', 'cbr_rate','factor'], 'number','numberPattern' => '/^\s*[-+]?[0-9]*[.,]?[0-9]+([eE][-+]?[0-9]+)?\s*$/'],
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
        $query = ExchangeRates::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
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
        $nbrb_rate = str_replace(",", ".", $this->nbrb_rate);
        if($this->nbrb_rate_old) {
            $nbrb_rate_old = $this->nbrb_rate_old/10000;
        }
        $query->andFilterWhere([
            'id' => $this->id,
            'nbrb' => $this->nbrb,
            'cbr' => $this->cbr,
            'nbrb_rate' => $nbrb_rate,
            'cbr_rate' => $this->cbr_rate,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'show_at_widget' => $this->show_at_widget
        ]);
        $query->andFilterWhere([
            'nbrb_rate' => $this->nbrb_rate_old,

        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'code', $this->code]);

        return $dataProvider;
    }
}
