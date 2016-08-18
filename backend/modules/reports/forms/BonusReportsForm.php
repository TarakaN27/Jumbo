<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 29.3.16
 * Time: 10.20
 */

namespace backend\modules\reports\forms;


use backend\models\BUser;
use common\components\helpers\CustomHelper;
use common\models\BonusScheme;
use common\models\BUserBonus;
use common\models\BUserPaymentRecords;
use common\models\CUser;
use common\models\CUserRequisites;
use common\models\ExchangeRates;
use common\models\Payments;
use common\models\Services;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class BonusReportsForm extends Model
{
	public
		$bonusType,
		$scheme,
		$service,
		$beginDate,
		$endDate,
		$users;

	/**
	 * @return array
	 */
	public function rules()
	{
		return [
			[['bonusType','scheme','service'],'integer'],
			['users','required'],
			[['beginDate','endDate'],'required'],
			['users','each','rule' => ['integer']],
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
			])
			->joinWith('buser')
			->joinWith('cuser')
			->joinWith('cuser.requisites')
			->joinWith('service')
			->joinWith('payment')
			->joinWith('scheme')
			->joinWith('payment.currency')
			->where([BUserBonus::tableName().'.buser_id' => $this->users])
			->andWhere(Payments::tableName().'.pay_date >= :beginDate AND '.Payments::tableName().'.pay_date <= :endDate')
			->params([
				':beginDate' => strtotime($this->beginDate.' 00:00:00'),
				':endDate' => strtotime($this->endDate.' 23:59:59')
			]);

		$query->andFilterWhere([
			BonusScheme::tableName().'.type' => $this->bonusType,
			BUserBonus::tableName().'.scheme_id' => $this->scheme,
			BUserBonus::tableName().'.service_id' => $this->service
		]);

		return [
			'dataProvider' => new ActiveDataProvider([
					'query' => $query,
					'pagination' => [
						'pageSize' => -1,
					],
					//'sort'=> ['defaultOrder' => ['pay_date'=>SORT_ASC]],
				]),
			'totalCount' => $query->sum('amount'),
			'bonusPaymentRecords' => $this->getPaymentsRecordsBonus()
		];
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