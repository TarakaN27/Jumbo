<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 1.4.16
 * Time: 16.18
 */

namespace common\components\crunchs\bonus;


use common\models\BUserBonus;
use common\models\Payments;
use common\models\PaymentsSale;
use common\components\helpers\CustomHelper;
use common\models\BonusScheme;
use common\models\BonusSchemeExceptCuser;
use common\models\BonusSchemeServiceHistory;
use common\models\BonusSchemeToBuser;
use common\models\BonusSchemeToCuser;
use common\models\ExchangeCurrencyHistory;
use common\models\managers\PaymentsManager;
use common\models\PaymentCondition;
use common\models\PaymentsCalculations;
use yii\web\ServerErrorHttpException;
use Yii;

class RecalculateBonus
{



	public function run()
	{
		$arSales = PaymentsSale::find()->all();

		$arPID = [];
		foreach($arSales as $sale)
		{
			$arPID[] = $sale->payment_id;
		}

		$arPaymentTmp = Payments::find()->where(['id' => $arPID])->all();
		$arPayment = [];
		foreach($arPaymentTmp as $tmp)
			$arPayment [$tmp->id] = $tmp;

		/** @var PaymentsSale $sale */
		foreach($arSales as $key => $sale)
		{
			if(!isset($arPayment[$sale->payment_id]))
				continue;

			$this->countingSimpleBonus($arPayment[$sale->payment_id],$sale->buser_id);
			$this->countingComplexBonus($arPayment[$sale->payment_id],$sale->buser_id);
		}
	}


	protected function countingSimpleBonus(Payments $model,$saleUser)
	{
		$obScheme = BonusScheme::find()  //получаем схему бонуса для пользователя с заднной компанией.
			->joinWith('cuserID')
			->joinWith('usersID')
			->joinWith('exceptCusers')
			->where([
				BonusScheme::tableName().'.type' => BonusScheme::TYPE_SIMPLE_BONUS,
				BonusSchemeToBuser::tableName().'.buser_id' => $saleUser,
				BonusSchemeToCuser::tableName().'.cuser_id' => $model->cuser_id
			])
			->andWhere(BonusSchemeExceptCuser::tableName().'.cuser_id != :idCUser OR  '.BonusSchemeExceptCuser::tableName().'.cuser_id IS NULL')
			->params([':idCUser' => $model->cuser_id])
			->orderBy([
				BonusScheme::tableName().'.updated_at' => SORT_DESC
			])
			->one();
		if(!$obScheme)
			$obScheme = BonusScheme::find()  //получаем схему бонуса для пользователя.
				->joinWith('cuserID')
				->joinWith('usersID')
				->joinWith('exceptCusers')
				->where([
					BonusScheme::tableName().'.type' => BonusScheme::TYPE_SIMPLE_BONUS,
					BonusSchemeToBuser::tableName().'.buser_id' => $saleUser,
				])
				->andWhere(BonusSchemeToCuser::tableName().'.scheme_id IS NULL')
				->andWhere(BonusSchemeExceptCuser::tableName().'.cuser_id != :idCUser OR  '.BonusSchemeExceptCuser::tableName().'.cuser_id IS NULL')
				->params([':idCUser' => $model->cuser_id])
				->orderBy([
					BonusScheme::tableName().'.updated_at' => SORT_DESC
				])
				->one();

		if(empty($obScheme))
			return FALSE;

		//костыли
		if($obScheme->id == 4 && $model->pay_date < strtotime('01.03.2016') && in_array($model->cuser_id,[170,8768]))
		{
			return false;
		}



		$obBServ = BonusSchemeServiceHistory::getCurrentBonusService($model->pay_date,$model->service_id,$obScheme->id);    //получаем параметры схемы

		if(empty($obBServ) || empty($obBServ->simple_percent))
			return FALSE;

		$amount = $this->getAmount($model);
		if(empty($amount))
			return FALSE;

		if(is_array($obBServ->legal_person) &&  //проверяем не указано ли для Юр. лица отнимать НАЛОГ от платежа
			isset($obBServ->legal_person[$model->legal_id]) &&
			$obBServ->legal_person[$model->legal_id] == 1)
		{
			$amount = CustomHelper::getVatMountByAmount($amount); //отнимем от суммы платежа налог
		}

		$amount = round($amount*($obBServ->simple_percent/100),6);  //начисляем процент

		return $this->addBonus($saleUser,$model->id,$obScheme->id,$model->service_id,$model->cuser_id,$amount);  //добавим бонус
	}

	/**
	 * @return float|null
	 */
	protected function getAmount(Payments $model)
	{
		$amount = NULL;
		//для кастомных условий, бонус считается от прибыли
		if(PaymentCondition::find()->where(['id' => $model->condition_id,'type' => PaymentCondition::TYPE_CUSTOM])->exists())
		{
			/** @var PaymentsCalculations $obCalc */
			$obCalc = $model->calculate;
			if($obCalc)
			{
				$amount = $obCalc->profit;
			}
		}else{  //для обычных условий бонус считается из платежа
			$excRate = ExchangeCurrencyHistory::getCurrencyInBURForDate($model->pay_date,$model->currency_id);  //получаем курс валюты в бел. рублях
			$amount = $excRate*$model->pay_summ;    //получаем сумму платежа в бел. рублях
		}

		return $amount;
	}


	/**
	 * Составной бонус
	 * @param $model
	 * @return bool
	 * @throws ServerErrorHttpException
	 */
	protected function countingComplexBonus(Payments $model,$saleUser)
	{
		$arCuserGroup = PaymentsManager::getUserByGroup($model->cuser_id);  //получаем пользователей группы
		$inActivePeriod = (int)Yii::$app->config->get('c_inactivity',0);  //период бездействия в месяцах

		$obSale = PaymentsSale::find()
			->where(['cuser_id' => $arCuserGroup])
			->orderBy(['sale_date' => SORT_ASC,'id' => SORT_ASC])
			->one();

		if(!empty($obSale))
			$saleUser = $obSale->buser_id;

		$obScheme = BonusScheme::find()  //ищем схему для компании
			->joinWith('cuserID')
			->joinWith('usersID')
			->joinWith('exceptCusers')
			->where([
				BonusScheme::tableName().'.type' => BonusScheme::TYPE_COMPLEX_TYPE,
				BonusSchemeToBuser::tableName().'.buser_id' => $saleUser,
				BonusSchemeToCuser::tableName().'.cuser_id' => $model->cuser_id
			])
			->andWhere(BonusSchemeExceptCuser::tableName().'.cuser_id != :idCUser OR  '.BonusSchemeExceptCuser::tableName().'.cuser_id IS NULL')
			->params([':idCUser' => $model->cuser_id])
			->orderBy([BonusScheme::tableName().'.updated_at' => SORT_DESC])
			->one();

		if(!$obScheme)  //если нет схемы для компании, ищем общую
			$obScheme = BonusScheme::find()  //получаем схему бонуса для пользователя.
				->joinWith('cuserID')
				->joinWith('usersID')
				->joinWith('exceptCusers')
				->where([BonusScheme::tableName().'.type' => BonusScheme::TYPE_COMPLEX_TYPE])
				->andWhere([BonusSchemeToBuser::tableName().'.buser_id' => $saleUser])
				->andWhere(BonusSchemeExceptCuser::tableName().'.cuser_id != :idCUser OR  '.BonusSchemeExceptCuser::tableName().'.cuser_id IS NULL')
				->andWhere(BonusSchemeToCuser::tableName().'.scheme_id IS NULL')
				->orderBy([BonusScheme::tableName().'.updated_at' => SORT_DESC])
				->params([':idCUser' => $model->cuser_id])
				->one();

		if(empty($obScheme))
			return FALSE;

		//костыли
		if(in_array($model->cuser_id,[6517,8753,208]) && $obScheme->id == 4 && $model->pay_date < strtotime('01.02.2016'))
		{
			return false;
		}

		if(!in_array($model->cuser_id,[6517,8753,208,170,8768]) && $model->pay_date < strtotime('01.03.2016'))
			return FALSE;

		$obBServ = BonusSchemeServiceHistory::getCurrentBonusService($model->pay_date,$model->service_id,$obScheme->id);    //получаем параметры бонусов на дату платежа

		if(empty($obBServ))
			return FALSE;

		$lastPayment = Payments::find() //ищем последнюю оплату по клиенту(если не было, значит продажа)
			->select(['pay_date'])
			->where(['cuser_id' => $arCuserGroup])
			->andWhere('id != :ID and pay_date <= :payDate')
			->params([
				':ID' => $model->id,
				':payDate' => $model->pay_date
			])
			->orderBy(['pay_date' => SORT_DESC])
			->one();

		$percent = NULL; //определим процент для бонуса
		if(empty($lastPayment))
		{
			if(is_array($obBServ->month_percent) && isset($obBServ->month_percent[1]) && !empty($obBServ->month_percent[1]))
				$percent = $obBServ->month_percent[1];
		}else{

			$beginDate = CustomHelper::getDateMinusNumMonth(time(),$inActivePeriod);
			if($lastPayment->pay_date > $beginDate)
			{
				$payMonth = PaymentsManager::getPaymentMonth($model->service_id,$model->cuser_id,$model->pay_date,TRUE);

				if(!is_null($payMonth) && is_array($obBServ->month_percent) && isset($obBServ->month_percent[$payMonth+1]) && !empty($obBServ->month_percent[$payMonth+1]))
					$percent = $obBServ->month_percent[$payMonth+1];
			}else{
				if(is_array($obBServ->month_percent) && isset($obBServ->month_percent[1]) && !empty($obBServ->month_percent[1]))
					$percent = $obBServ->month_percent[1];
			}
		}

		if(empty($percent))
			return FALSE;

		$amount = $this->getAmount($model);
		if(empty($amount))
			return FALSE;
		else
			$amount = $amount*($percent/100);

		if(is_array($obBServ->legal_person) &&  //если ля юр. лица указан, что нужно отнять налог
			isset($obBServ->legal_person[$model->legal_id]) &&
			$obBServ->legal_person[$model->legal_id] == 1)
		{
			$amount = CustomHelper::getVatMountByAmount($amount);	  //отнимаем налог
		}

		return $this->addBonus($saleUser,$model->id,$obScheme->id,$model->service_id,$model->cuser_id,$amount);  //добавляем бонус
	}


	/**
	 * Добавляем бонус
	 * @param $iUserID
	 * @param $iPaymentID
	 * @param $iSchemeID
	 * @param $amount
	 * @return bool
	 * @throws ServerErrorHttpException
	 */
	protected function addBonus($iUserID,$iPaymentID,$iSchemeID,$iServiceID,$iCuserID,$amount){
		$obBonus = new BUserBonus();
		$obBonus->amount = $amount;
		$obBonus->buser_id = $iUserID;
		$obBonus->payment_id = $iPaymentID;
		$obBonus->scheme_id = $iSchemeID;
		$obBonus->service_id = $iServiceID;
		$obBonus->cuser_id = $iCuserID;
		if(!$obBonus->save())
			throw new ServerErrorHttpException('Error. Can not save bonus');
		return $obBonus;
	}

}