<?php

namespace common\models\search;

use common\models\CUser;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\PartnerWithdrawalRequest;

/**
 * PartnerWithdrawalRequestSearch represents the model behind the search form about `common\models\PartnerWithdrawalRequest`.
 */
class PartnerWithdrawalRequestSearch extends PartnerWithdrawalRequest
{

    public
        $partnerManager,
        $manager,
        $created_at_from,
        $created_at_to,
        $from_date,
        $to_date;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[
                'id', 'partner_id', 'type',
                'currency_id', 'manager_id', 'created_by',
                'status', 'created_at', 'updated_at',
                'partnerManager'
            ], 'integer'],
            [['amount'], 'number'],
            [['from_date','to_date','created_at_from','created_at_to'],'date','format' => 'php:d.m.Y']
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
    public function search($params,$addCond = NULL)
    {
        $query = PartnerWithdrawalRequest::find()->joinWith('partner');

        if(!is_null($addCond))
            $query = $query->where($addCond);


        $tableNamePWR = PartnerWithdrawalRequest::tableName();      //getTableName
        $tablePartner = CUser::tableName();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $dataProvider->sort->attributes['partnerManager'] = [
            // The tables are the ones our relation are configured to
            // in my case they are prefixed with "tbl_"
            'asc' => [$tablePartner.'.partner_manager_id' => SORT_ASC],
            'desc' => [$tablePartner.'.partner_manager_id' => SORT_DESC],
        ];


        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            $tableNamePWR.'.id' => $this->id,
            $tableNamePWR.'.partner_id' => $this->partner_id,
            $tableNamePWR.'.type' => $this->type,
            $tableNamePWR.'.amount' => $this->amount,
            $tableNamePWR.'.currency_id' => $this->currency_id,
            $tableNamePWR.'.manager_id' => $this->manager_id,
            $tableNamePWR.'.created_by' => $this->created_by,
            $tablePartner.'.partner_manager_id' => $this->partnerManager,
            //'date' => $this->date,
            $tableNamePWR.'.status' => $this->status,
            $tableNamePWR.'.created_at' => $this->created_at,
            $tableNamePWR.'.updated_at' => $this->updated_at,
        ]);

        if(!empty($this->from_date))
            $query->andWhere($tableNamePWR.".date >= :dateFrom",[':dateFrom' => strtotime($this->from_date.' 00:00:00')]);

        if(!empty($this->to_date))
            $query->andWhere($tableNamePWR.".date <= :dateTo",[':dateTo' => strtotime($this->to_date.' 23:59:59')]);

        if(!empty($this->created_at_from))
            $query->andWhere($tableNamePWR.".created_at >= :dateFromCr",[':dateFromCr' => strtotime($this->created_at_from.' 00:00:00')]);

        if(!empty($this->created_at_to))
            $query->andWhere($tableNamePWR.".created_at <= :dateToCr",[':dateToCr' => strtotime($this->created_at_to.' 23:59:59')]);

        return $dataProvider;
    }
}
