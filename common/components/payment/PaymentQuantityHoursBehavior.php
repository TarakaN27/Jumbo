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
use common\models\Services;
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
			//ActiveRecord::EVENT_AFTER_DELETE => 'afterDelete',
			Payments::EVENT_AFTER_INSERT => 'afterInsert',
			Payments::EVENT_AFTER_UPDATE => 'afterUpdate',
			//ActiveRecord::EVENT_BEFORE_UPDATE => 'beforeUpdate',
		];
	}

	/**
	 * @return bool|string
	 */
	protected function getFormatedDate()
	{
		return date('Y-mm-dd',$this->owner->pay_date);
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

		$obQHour = CuserQuantityHour::find()->where(['cuser_id' => $model->cuser_id])->one();
		if(!$obQHour)
		{
			$obQHour = new CuserQuantityHour();
			$obQHour->spent_time = 0;
			$obQHour->cuser_id = $model->cuser_id;
		}

		$date = $this->getFormatedDate();
		$rate = ServiceRateHist::getRateForDate($model->service_id,$date);

		if($rate > 0 )
		{
			$currBUR = ExchangeCurrencyHistory::getCurrencyInBURForDate($date,$model->currency_id);
			$amount = $model->pay_summ*$currBUR;
			$hours = round($amount/$rate,2);
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
		$this->oldServID = $model->service_id;
		$this->oldCurrID = $model->currency_id;
		$this->oldPaySumm = $model->pay_summ;
		$this->oldDate = $model->pay_date;
		return TRUE;
	}

	public function afterUpdate()
	{
		/** @var Payments $model */
		$model = $this->owner;
		$obQHour = CuserQuantityHour::find()->where(['cuser_id' => $model->cuser_id])->one();






	}





}