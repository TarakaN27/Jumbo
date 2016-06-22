<?php

namespace common\models\search;

use common\models\BillServices;
use common\models\CUser;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Bills;
use yii\helpers\ArrayHelper;

/**
 * BillsSearch represents the model behind the search form about `common\models\Bills`.
 */
class BillsSearch extends Bills
{
    public
        $bill_services,
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
            [[
                'id', 'manager_id', 'cuser_id',
                'l_person_id', 'service_id', 'docx_tmpl_id',
                'amount', 'bill_number', 'bill_template',
                'use_vat', 'created_at', 'updated_at','bill_services'
            ], 'integer'],
            [['bill_date', 'description', 'object_text', 'buy_target','from_date','to_date'], 'safe'],
            [['vat_rate'], 'number'],
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
    public function search($params)
    {
        $query = Bills::find()->with('service','cuser','lPerson','docxTmpl');
        $query = $this->queryHelper($query,$params);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'defaultPageSize' => Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => [1,1000]
            ],
            'sort'=> ['defaultOrder' => ['updated_at'=>SORT_DESC]]
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
     * @return mixed
     */
    protected function queryHelper($query,$params)
    {
        if(Yii::$app->user->can('only_manager'))
        {
            $query->joinWith('cuser');
            $query->where([CUser::tableName().'.manager_id' => Yii::$app->user->id]);
        }

        $query->joinWith('billServices');
        $this->load($params);

        $query->andFilterWhere([
            self::tableName().'.id' => $this->id,
            self::tableName().'.manager_id' => $this->manager_id,
            self::tableName().'.cuser_id' => $this->cuser_id,
            self::tableName().'.l_person_id' => $this->l_person_id,
            self::tableName().'.service_id' => $this->service_id,
            self::tableName().'.docx_tmpl_id' => $this->docx_tmpl_id,
            self::tableName().'.amount' => $this->amount,
            self::tableName().'.bill_number' => $this->bill_number,
            //'bill_date' => $this->bill_date,
            self::tableName().'.bill_template' => $this->bill_template,
            self::tableName().'.use_vat' => $this->use_vat,
            self::tableName().'.vat_rate' => $this->vat_rate,
            self::tableName().'.created_at' => $this->created_at,
            self::tableName().'.updated_at' => $this->updated_at,
        ]);

        if(!empty($this->bill_date))
            $query->andFilterWhere([
                self::tableName().'.bill_date' => date('Y-m-d',strtotime($this->bill_date))
            ]);
        
        $query->andFilterWhere(['like', self::tableName().'.description', $this->description])
            ->andFilterWhere(['like', self::tableName().'.object_text', $this->object_text])
            ->andFilterWhere(['like', self::tableName().'.buy_target', $this->buy_target]);

        if(!empty($this->from_date))
            $query->andWhere(self::tableName().'.bill_date >= :dateFrom',[':dateFrom' => date('Y-m-d',strtotime($this->from_date))]);

        if(!empty($this->to_date))
            $query->andWhere(self::tableName().'.bill_date <= :dateTo',[':dateTo' => date('Y-m-d',strtotime($this->to_date))]);

        if(!empty($this->bill_services))
        {
            $query->andWhere(self::tableName().'.service_id = :service OR '.BillServices::tableName().'.service_id = :service',[':service' => $this->bill_services]);
        }

        if(
            !empty($this->manager_id)||
            !empty($this->cuser_id)||
            !empty($this->l_person_id)||
            !empty($this->service_id)||
            !empty($this->docx_tmpl_id)||
            !empty($this->bill_template)||
            !empty($this->created_at)||
            !empty($this->from_date)||
            !empty($this->to_date)||
            !empty($this->bill_services)
        )
            $this->bCountTotal = TRUE;



        return $query;
    }

    /**
     * @param $params
     * @return int|null
     */
    public function countTotal($params)
    {
        $query = Bills::find()->select([self::tableName().'.amount']);
        $query = $this->queryHelper($query,$params);
        if(!$this->bCountTotal)
            return NULL;

        $arBill = $query->all();
        if(empty($arBill))
            return NULL;

        $iRes = 0;
        foreach($arBill as $bill)
            $iRes+=$bill->amount;

        return $iRes;
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        $arLabels =  parent::attributeLabels(); // TODO: Change the autogenerated stub
        return ArrayHelper::merge($arLabels,[
            'bill_services' => Yii::t('app/documents','Service')
        ]);
    }
}
