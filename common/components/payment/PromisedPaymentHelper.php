<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 20.10.15
 * Time: 13.53
 */

namespace common\components\payment;


use common\components\helpers\CustomHelper;
use common\models\CuserSettings;
use common\models\ExchangeRates;
use common\models\Payments;
use yii\base\InvalidParamException;
use yii\helpers\ArrayHelper;

class PromisedPaymentHelper
{
	protected
		$userID,
		$serviceID;

	public static
		$period = 3;    //период


	public function __construct($userID,$serviceID = NULL)
	{
		if( empty($userID) || !is_numeric($userID))
			throw new InvalidParamException('userID must be an integer');

		$this->userID = (int)$userID;
		$this->serviceID = (int)$serviceID;
	}

	/**
	 * @return float|int
	 */
	public function getMaxAmount()
	{
		/** @var CuserSettings $obCuSettings */
		$obCuSettings = CuserSettings::findOne(['cuser_id' => $this->userID]);
		if(!empty($obCuSettings))
		{
			if(!empty($obCuSettings->pp_max))
				return $obCuSettings->pp_max;

			if(!empty($obCuSettings->pp_percent))
				return $this->maxAmountHelper($this->userID,$obCuSettings->pp_percent);
		}

		return $this->maxAmountHelper($this->userID,\Yii::$app->config->get('pp_percent'));
	}

	/**
	 *
	 * @param $userID
	 * @param $percent
	 * @return float|int
	 */
	protected function maxAmountHelper($userID,$percent)
	{
		$period = $this->getPeriod();

		$obPayments = Payments::find()->where(['cuser_id' => $userID,'service_id' => $this->serviceID])
			->andWhere('pay_date > :pay_date',[':pay_date' => $period])
			->all();

		if(empty($obPayments))
			return 0;

		return $this->amountHelper($obPayments,$percent);
	}

	/**
	 * Получаем обменные курсы
	 * @param $obPayments
	 * @return array
	 */
	protected function currencyHelper($obPayments)
	{
		$arCurrency = [];
		foreach($obPayments as $pay)
			$arCurrency [] = $pay->currency_id;
		$arCurrency = array_unique($arCurrency);
		$obCurrency = ExchangeRates::findAll(['id' => $arCurrency]);
		return ArrayHelper::map($obCurrency,'id','nbrb_rate');
	}

	/**
	 * @param array $arServices
	 * @param $userID
	 * @return array
	 */
	public function getMaxAmountForServices(array $arServices)
	{
		$period = $this->getPeriod();
		$obPayList = Payments::getPaymentByLastPeriod($this->userID,$period); //получаем платежи пользователя за последние 3 месяца.

		if(empty($obPayList))   //нет платежей вернем 0 суммы
			return $this->fillByValue($arServices,0);

		/** @var CuserSettings $obCuSettings */
		$obCuSettings = CuserSettings::findOne(['cuser_id' => $this->userID]);  //пользовательские настройки

		if($obCuSettings && !empty($obCuSettings->pp_max)) {    //если задана фикс. сумма об. платежа для пользователя
			$arAllowServ = [];
			/** @var Payments $pay */
			foreach($obPayList as $pay) //проходим по платежам и собираем услуги
			{
				if(!in_array($pay->service_id,$arAllowServ))
					$arAllowServ [] = $pay->service_id;
			}

			$arDisallowSerrv = array_diff($arServices,$arAllowServ);    //выберем услуги для которых нет платежей

			$arDisallowSerrv = $this->fillByValue($arDisallowSerrv,0); //для услуг нет платежей, поэтому разрешенная сумма  = 0
			$arAllowServ = $this->fillByValue($arAllowServ,$obCuSettings->pp_max); // для услуг есть платежи, но указаан фикс. сумма для пользователя.

			return ArrayHelper::merge($arAllowServ,$arDisallowSerrv);
		}

		if($obCuSettings && !empty($obCuSettings->pp_percent))  // если задан индивидуальный процент по об. платежу
			return $this->maxAmountServicesHelper($arServices,$obPayList,$obCuSettings->pp_percent);

		return $this->maxAmountServicesHelper($arServices,$obPayList,\Yii::$app->config->get('pp_percent'));
	}

	/**
	 * Хелпер вычисляет по платежам досутпную сумма для обещенного платежа по услугам
	 * @param array $arServices
	 * @param array $arPayments
	 * @param $percent
	 * @return array
	 */
	protected function maxAmountServicesHelper(array $arServices, array $arPayments,$percent)
	{
		$arCurr = $this->currencyHelper($arPayments);   //получаем значение курса валют

		$arPayServ = [];
		/** @var Payments $pay */
		foreach($arPayments as $pay)    //проходим по платежам и собираем сумму платежей по каждой услуге
		{
			if(isset($arCurr[$pay->currency_id]))   //учитываем перевод из валюты
				$arPayServ [$pay->service_id] =
					isset($arPayServ [$pay->service_id]) ?
						$arPayServ [$pay->service_id] + $pay->pay_summ*$arCurr[$pay->currency_id] :
						$pay->pay_summ*$arCurr[$pay->currency_id];
		}

		$arDisallServ = array_diff($arServices,array_keys($arPayServ)); // получаем услуги по которым не было платежей

		$arDisallServ = $this->fillByValue($arDisallServ,0);    //заполняем 0 суммами услуги без платежей

		foreach($arPayServ as &$ps) // рассчитываем доступную сумму для услуг, у которых были платежи
		{
			$ps = CustomHelper::roundBy50000UP($ps*$percent/100);   //округляем до 50000
		}

		return ArrayHelper::merge($arDisallServ,$arPayServ);   //возвращаем все услуги с указаными лимитами
	}

	/**
	 * Заполение массива значения по ключам
	 * @param array $arServices
	 * @param $value
	 * @return array
	 */
	protected function fillByValue(array $arServices,$value)
	{
		$arRtn = [];
		foreach($arServices as $serv)
			$arRtn [$serv] = $value;
		return $arRtn;
	}

	/**
	 * @return int
	 */
	protected function getPeriod()
	{
		return time()-3600*24*31*self::$period;
	}

	/**
	 * @param $userID
	 * @param $amount
	 * @param $serviceID
	 * @return bool
	 */
	public function isAllowedAmount($userID,$amount,$serviceID)
	{
		$period = $this->getPeriod();
		$arPayments = Payments::find()
			->where('pay_date > :pay_date',[':pay_date' => $period])
			->andWhere(['cuser_id' => $userID,'service_id' => $serviceID])->all();

		if(!$arPayments)
			return FALSE;

		/** @var CuserSettings $obCuSettings */
		$obCuSettings = CuserSettings::findOne(['cuser_id' => $this->userID]);  //пользовательские настройки

		if($obCuSettings && !empty($obCuSettings->pp_max))  //задана фикс. сумма
			return $amount > 0 && $amount <= $obCuSettings->pp_max;

		if($obCuSettings && !empty($obCuSettings->pp_percent)) //задан индивид процент
			$percent = $obCuSettings->pp_percent;
		else
			$percent = (int)\Yii::$app->config->get('pp_percent'); // считаем по общему проценту

		$cAmount = $this->amountHelper($arPayments,$percent); // рассчитаем доступную сумму
		return $amount > 0 && $amount <= $cAmount;  //сравним
	}

	/**
	 * @param array $arPayments
	 * @param $percent
	 * @return float
	 */
	protected function amountHelper(array $arPayments,$percent)
	{
		$arCurr = $this->currencyHelper($arPayments);   //получаем значение курса валют

		$shareAmount = 0;
		foreach($arPayments as $payment)
		{
			if(isset($arCurr[$payment->currency_id]))
				$shareAmount += $payment->pay_summ*$arCurr[$payment->currency_id];
		}
		return CustomHelper::roundBy50000UP($shareAmount*$percent/100);
	}
}