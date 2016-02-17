<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 16.2.16
 * Time: 12.50
 */

namespace common\components\payment;

use common\models\CuserQuantityHour;
use common\models\ExchangeCurrencyHistory;
use common\models\ServiceRateHist;
use yii\base\Behavior;
use common\models\Payments;
use yii\web\ServerErrorHttpException;

class PaymentQuantityHoursBehavior extends Behavior
{
	protected
		$oldServID = NULL,
		$oldDate = NULL,
		$oldCurrID = NULL,
		$oldPaySumm = NULL;

	/**
	 * @return array
	 */
	public function events()
	{
		return [
			Payments::EVENT_AFTER_DELETE => 'afterDelete',
			Payments::EVENT_AFTER_INSERT => 'afterInsert',
			Payments::EVENT_AFTER_UPDATE => 'afterUpdate',
			Payments::EVENT_BEFORE_UPDATE => 'beforeUpdate',
		];
	}

	/**
	 * @return bool|string
	 */
	protected function getFormatedDate()
	{
		return date('Y-m-d',$this->owner->pay_date);
	}

	/**
	 * Начисляем норма часы за платеж
	 * @return bool
	 * @throws ServerErrorHttpException
	 */
	public function afterInsert()
	{
		/** @var Payments $model */
		$model = $this->owner;

		$obQHour = CuserQuantityHour::find()->where(['cuser_id' => $model->cuser_id])->one();   //находим нормачасы
		if(!$obQHour)   //не заведены, добавим
		{
			$obQHour = new CuserQuantityHour();
			$obQHour->spent_time = 0;
			$obQHour->cuser_id = $model->cuser_id;
		}

		$date = $this->getFormatedDate();   //форматирвоанная дата
		$rate = ServiceRateHist::getRateForDate($model->service_id,$date);  //Получаем ставку норма часа на дату платежа

		if($rate > 0 )  //есть ставка, продолжим
		{
			$currBUR = ExchangeCurrencyHistory::getCurrencyInBURForDate($date,$model->currency_id); //курс валюта на заданное число
			$amount = $model->pay_summ*$currBUR;
			$hours = round($amount/$rate,2);    //вычисляем кол-во часов
			$obQHour->hours+=$hours;
			if(!$obQHour->save())
			{
				throw new ServerErrorHttpException('Can not save the required quantity of hours');
			}
		}

		return TRUE;
	}

	/**
	 * @return bool
	 * @throws ServerErrorHttpException
	 */
	public function afterDelete()
	{
		/** @var Payments $model */
		$model = $this->owner;

		$obQHour = CuserQuantityHour::find()->where(['cuser_id' => $model->cuser_id])->one();
		if(!$obQHour)
			return TRUE;

		$date = $this->getFormatedDate();
		$rate = ServiceRateHist::getRateForDate($model->service_id,$date);

		if($rate > 0 )
		{
			$currBUR = ExchangeCurrencyHistory::getCurrencyInBURForDate($date,$model->currency_id);
			$amount = $model->pay_summ*$currBUR;
			$hours = round($amount/$rate,2);
			$obQHour->hours-=$hours;
			if(!$obQHour->save())
			{
				throw new ServerErrorHttpException('Can not save the required quantity of hours');
			}
		}

		return TRUE;
	}

	/**
	 * @return bool
	 */
	public function beforeUpdate()
	{
		/** @var Payments $model */
		$model = $this->owner;
		$this->oldServID = $model->getOldAttribute('service_id');
		$this->oldCurrID = $model->getOldAttribute('currency_id');
		$this->oldPaySumm = $model->getOldAttribute('pay_summ');
		$this->oldDate = date('Y-m-d',$model->getOldAttribute('pay_date'));
		return TRUE;
	}

	/**
	 * @return bool
	 * @throws ServerErrorHttpException
	 */
	public function afterUpdate()
	{
		/** @var Payments $model */
		$model = $this->owner;
		$obQHour = CuserQuantityHour::find()->where(['cuser_id' => $model->cuser_id])->one();
		$hours = 0;
		//отнимем старые начисления
		if($obQHour)
		{
			$rate = ServiceRateHist::getRateForDate($this->oldServID,$this->oldDate);
			if($rate > 0)
			{
				$currBUR = ExchangeCurrencyHistory::getCurrencyInBURForDate($this->oldDate,$this->oldCurrID);
				$amount = $this->oldPaySumm*$currBUR;
				$hours = -round($amount/$rate,2);
				unset($rate,$currBUR,$amount);
			}
		}else{
			$obQHour = new CuserQuantityHour();
			$obQHour->spent_time = 0;
			$obQHour->cuser_id = $model->cuser_id;
		}

		//добавим новые
		$date = $this->getFormatedDate();
		$rate = ServiceRateHist::getRateForDate($model->service_id,$date);
		if($rate > 0 )
		{
			$currBUR = ExchangeCurrencyHistory::getCurrencyInBURForDate($date,$model->currency_id);
			$amount = $model->pay_summ*$currBUR;
			$hours += round($amount/$rate,2);
			$obQHour->hours+=$hours;
			if(!$obQHour->save())
			{
				throw new ServerErrorHttpException('Can not save the required quantity of hours');
			}
		}

		return TRUE;
	}
}