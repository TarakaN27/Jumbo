<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 29.3.16
 * Time: 10.20
 */

namespace backend\modules\reports\forms;


use backend\models\BUser;
use common\components\helpers\CustomDateHelper;
use common\components\helpers\CustomHelper;
use common\models\BonusScheme;
use common\models\BUserBonus;
use common\models\BUserBonusMonthCoeff;
use common\models\BUserPaymentRecords;
use common\models\CUser;
use common\models\CUserRequisites;
use common\models\ExchangeRates;
use common\models\Payments;
use common\models\PaymentsCalculations;
use common\models\PaymentsSale;
use common\models\Services;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

class BonusReportsForm extends Model
{
	public
		$bonusType,
		$scheme,
		$service,
		$beginDate,
		$endDate,
		$users,
		$cusers;

	/**
	 * @return array
	 */
	public function rules()
	{
		return [
			[['bonusType','scheme','service'],'integer'],
			['users','required'],
			[['beginDate','endDate'],'required'],
			[['users','cusers'],'each','rule' => ['integer']],
			[['beginDate','endDate'],'date','format' => 'php:d.m.Y'],
			[['beginDate','endDate'],'customValidate'],
		];
	}

	public function customValidate($attribute,$params)
	{
		if(strtotime($this->beginDate) > strtotime($this->endDate))
			$this->addError($attribute,\Yii::t('app/bonus','End date must be more than begin date'));
	}

	/**
	 * @return array
	 */
	public function attributeLabels()
	{
		return [
			'beginDate' => \Yii::t('app/bonus','Begin date'),
			'endDate' => \Yii::t('app/bonus','End date'),
			'users' => \Yii::t('app/bonus','Users'),
			'cusers' => \Yii::t('app/bonus','Cusers'),
			'bonusType' => \Yii::t('app/bonus','Bonus type'),
			'scheme' => \Yii::t('app/bonus','Scheme'),
			'service' => \Yii::t('app/bonus','Service')
		];
	}

	/**
	 * @return array
	 */
	public function makeRequest()
	{
		$query = BUserBonus::find()
			->select([
				BUserBonus::tableName().'.buser_id',
				BUserBonus::tableName().'.service_id',
				BUserBonus::tableName().'.cuser_id',
				BUserBonus::tableName().'.payment_id',
				BUserBonus::tableName().'.scheme_id',
				BUserBonus::tableName().'.amount',
				BUserBonus::tableName().'.number_month',
				BUserBonus::tableName().'.bonus_percent',
				BUserBonus::tableName().'.is_sale',
				BUser::tableName().'.fname',
				BUser::tableName().'.lname',
				BUser::tableName().'.mname',
				Services::tableName().'.name as serv_name',
				CUser::tableName().'.requisites_id',
				CUserRequisites::tableName().'.type_id as req_type',
				CUserRequisites::tableName().'.corp_name',
				CUserRequisites::tableName().'.j_lname',
				CUserRequisites::tableName().'.j_mname',
				CUserRequisites::tableName().'.j_fname',
				Payments::tableName().'.pay_summ',
				Payments::tableName().'.pay_date',
				Payments::tableName().'.currency_id',
				ExchangeRates::tableName().'.code',
				BonusScheme::tableName().'.type as scheme_type',
				BonusScheme::tableName().'.name as scheme_name',
				PaymentsCalculations::tableName().'.profit_for_manager',
			])
			->joinWith('buser')
			->joinWith('cuser')
			->joinWith('cuser.requisites')
			->joinWith('service')
			->joinWith('payment')
			->joinWith('payment.sale')
			->joinWith('scheme')
			->joinWith('payment.currency')
			->joinWith('payment.calculate')
			->where([BUserBonus::tableName().'.buser_id' => $this->users])
			->andWhere(Payments::tableName().'.pay_date >= :beginDate AND '.Payments::tableName().'.pay_date <= :endDate')
			->params([
				':beginDate' => strtotime($this->beginDate.' 00:00:00'),
				':endDate' => strtotime($this->endDate.' 23:59:59')
			]);
        if($this->cusers != null)
            $query->andWhere([BUserBonus::tableName().'.cuser_id' => $this->cusers]);

		$query->andFilterWhere([
			BonusScheme::tableName().'.type' => $this->bonusType,
			BUserBonus::tableName().'.scheme_id' => $this->scheme,
			BUserBonus::tableName().'.service_id' => $this->service
		]);
		$totalProfitByUser = $this->calcTotalProfit();
		$totalProfitByUserType = [];
		$profitSchemes = BonusScheme::find()->where(['type'=>BonusScheme::TYPE_PROFIT_PAYMENT])->all();
		$correctCoeff = [];
		$searchCoeff = false;
		if($profitSchemes){
			foreach($profitSchemes as $schema) {
				$busers = $schema->users;
				foreach($busers as $user){
					if($schema->payment_base == BonusScheme::BASE_OWN_PAYMENT) {
						if (isset($totalProfitByUser[$user->id])) {
							$searchCoeff = true;
							$totalProfitByUserType['managers'][$user->id] = $totalProfitByUser[$user->id];
							$totalProfitByUserType['managers'][$user->id]['fio'] = $user->getFio();
						}
					}elseif($schema->payment_base == BonusScheme::BASE_ALL_PAYMENT_SALED_CLENT) {
							if (isset($totalProfitByUser[$user->id])) {
								$searchCoeff = true;
								$totalProfitByUserType['salers'][$user->id] = $totalProfitByUser[$user->id];
								$totalProfitByUserType['salers'][$user->id]['fio'] = $user->getFio();
							}
					}
				}
			}
			if($searchCoeff)
				$correctCoeff = BUserBonusMonthCoeff::getByUserAndDate($this->users, $this->beginDate, $this->endDate);
		}
		return [
			'dataProvider' => new ActiveDataProvider([
					'query' => $query,
					'pagination' => [
						'pageSize' => -1,
					],
					//'sort'=> ['defaultOrder' => ['pay_date'=>SORT_ASC]],
				]),
			'totalCount' => $query->sum('amount'),
			'totalProfitByUserType' => $totalProfitByUserType,
			'correctCoeff' => $correctCoeff,
//			'bonusPaymentRecords' => $this->getPaymentsRecordsBonus(),

		];
	}
	protected function calcTotalProfit(){
		$totalSumByUsers = [];
		foreach($this->users as $user){
			$totalSumByUsers[$user] = [];
		}
		$query = BUserBonus::find()
			->select(['totalSum'=>'SUM(profit_for_manager)', 'amount'=>'SUM(amount)',BUserBonus::tableName().'.buser_id'])
			->joinWith('payment.calculate')
			->joinWith('scheme')
			->where([BUserBonus::tableName().'.buser_id' => $this->users])
			->andWhere(Payments::tableName().'.pay_date >= :beginDate AND '.Payments::tableName().'.pay_date <= :endDate AND '.BUserBonus::tableName().'.number_month >1')
			->groupBy(BUserBonus::tableName().'.buser_id')
			->params([
				':beginDate' => strtotime($this->beginDate.' 00:00:00'),
				':endDate' => strtotime($this->endDate.' 23:59:59')
			]);

		$query->andFilterWhere([
			BonusScheme::tableName().'.type' => $this->bonusType,
			BUserBonus::tableName().'.scheme_id' => $this->scheme,
			BUserBonus::tableName().'.service_id' => $this->service
		]);
		$temp = $query->all();
		foreach($temp as $item){
			$totalSumByUsers[$item->buser_id]['sumWithoutNewClientCurrentPeriod'] = $item->totalSum;
		}
		$prevBeginDate = \DateTime::createFromFormat("d.m.Y", $this->beginDate)->modify("-1 month")->format('Y-m').'-01';
		$prevEndDate = \DateTime::createFromFormat("d.m.Y", $this->beginDate)->format('Y-m').'-01';

		$query = BUserBonus::find()
			->select(['totalSum'=>'SUM(profit_for_manager)', BUserBonus::tableName().'.buser_id'])
			->joinWith('payment.calculate')
			->joinWith('scheme')
			->where([BUserBonus::tableName().'.buser_id' => $this->users])
			->andWhere(Payments::tableName().'.pay_date >= :beginDate AND '.Payments::tableName().'.pay_date < :endDate AND '.BUserBonus::tableName().'.number_month >1')
			->groupBy(BUserBonus::tableName().'.buser_id')
			->params([
				':beginDate' => strtotime($prevBeginDate.' 00:00:00'),
				':endDate' => strtotime($prevEndDate.' 00:00:00')
			]);
		$query->andFilterWhere([
			BonusScheme::tableName().'.type' => $this->bonusType,
			BUserBonus::tableName().'.scheme_id' => $this->scheme,
			BUserBonus::tableName().'.service_id' => $this->service
		]);

		$temp = $query->all();
		foreach($temp as $item){
			$totalSumByUsers[$item->buser_id]['allSumPrevMonth'] =  $item->totalSum;;
		}

		$query = BUserBonus::find()
			->select(['totalSum'=>'SUM(profit_for_manager)', BUserBonus::tableName().'.buser_id'])
			->joinWith('payment.sale')
			->joinWith('payment.calculate')
			->joinWith('scheme')
			->where([BUserBonus::tableName().'.buser_id' => $this->users])
			->andWhere(Payments::tableName().'.pay_date >= :beginDate AND '.Payments::tableName().'.pay_date <= :endDate AND '.BUserBonus::tableName().'.is_sale=1')
			->groupBy(BUserBonus::tableName().'.buser_id')
			->params([
				':beginDate' => strtotime($this->beginDate.' 00:00:00'),
				':endDate' => strtotime($this->endDate.' 23:59:59')
			]);
		$query->andFilterWhere([
			BonusScheme::tableName().'.type' => $this->bonusType,
			BUserBonus::tableName().'.scheme_id' => $this->scheme,
			BUserBonus::tableName().'.service_id' => $this->service
		]);
		$temp = $query->all();
		foreach($temp as $item){
			$totalSumByUsers[$item->buser_id]['sumOnlySaleCurrentMonth'] =  $item->totalSum;
		}


		//общая сумма бонуса по юзерам
        $query = BUserBonus::find()
            ->select(['totalSum'=>'SUM(amount)', BUserBonus::tableName().'.buser_id'])
            ->joinWith('payment.calculate')
            ->joinWith('scheme')
            ->where([BUserBonus::tableName().'.buser_id' => $this->users])
            ->andWhere(Payments::tableName().'.pay_date >= :beginDate AND '.Payments::tableName().'.pay_date < :endDate')
            ->groupBy(BUserBonus::tableName().'.buser_id')
            ->params([
                ':beginDate' => strtotime($this->beginDate.' 00:00:00'),
                ':endDate' => strtotime($this->endDate.' 23:59:59')
            ]);
        $query->andFilterWhere([
            BonusScheme::tableName().'.type' => $this->bonusType,
            BUserBonus::tableName().'.scheme_id' => $this->scheme,
            BUserBonus::tableName().'.service_id' => $this->service
        ]);

        $temp = $query->all();
        foreach($temp as $item){
            $totalSumByUsers[$item->buser_id]['totalBonus'] = $item->totalSum;
        }


		return $totalSumByUsers;
	}
	/**
	 * @return ActiveDataProvider
	 */
	protected function getPaymentsRecordsBonus()
	{
		$beginDate = date('Y-m-d',CustomHelper::getBeginMonthTime(strtotime($this->beginDate.' 00:00:00')));
		$endDate = date('Y-m-d',CustomHelper::getBeginMonthTime(strtotime($this->endDate.' 00:00:00')));

		$query = BUserPaymentRecords::find()
			->with('bonus','buser','bonus.currency')
			->where([BUserPaymentRecords::tableName().'.buser_id' => $this->users])
			->andWhere(['BETWEEN',BUserPaymentRecords::tableName().'.record_date',$beginDate,$endDate]);

		$dataProvider =  new ActiveDataProvider([
			'query' => $query,
			'pagination' => [
				'pageSize' => 999
			],
			'sort'=> [
				'defaultOrder' => ['buser_id' => SORT_ASC, 'record_date'=>SORT_ASC]
			],
		]);
		$arDiffs = [];
		$arTmp = [];
		$models = $dataProvider->getModels();
		foreach ($models as $model)
		{
			if(isset($arTmp[$model->buser_id]))
			{
				$diff = CustomHelper::getDiffTwoNumbersAtPercent(end($arTmp[$model->buser_id]),$model->amount);
				$arDiffs[$model->id] = $diff;
				$arTmp[$model->buser_id][] = $model->amount;
			}else{
				$prevMonthBegin = (new \DateTime($beginDate))->modify("-1 month")->format("Y-m-d");
				$prevMonthEnd = (new \DateTime($prevMonthBegin))->format("Y-m-t");
				$tempRecord = BUserPaymentRecords::find()
					->where(['buser_id'=>$model->buser_id])
					->andWhere(['BETWEEN','record_date',$prevMonthBegin,$prevMonthEnd])
					->one();
				if($tempRecord){
					$diff = CustomHelper::getDiffTwoNumbersAtPercent($tempRecord->amount,$model->amount);
					$arDiffs[$model->id] = $diff;
					$arTmp[$model->buser_id][] = $model->amount;
				}else {
					$arDiffs[$model->id] = NULL;
					$arTmp[$model->buser_id][] = $model->amount;
				}
			}
		}
		unset($models,$arTmp,$query);
		return [
			'dataProvider' => $dataProvider,
			'diffs' => $arDiffs
		];
	}
}