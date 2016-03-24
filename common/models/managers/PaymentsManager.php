<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 24.3.16
 * Time: 11.13
 */

namespace common\models\managers;


use common\components\helpers\CustomHelper;
use common\models\CuserToGroup;
use common\models\Payments;
use common\models\PaymentsSale;
use common\models\Services;
use yii\web\NotFoundHttpException;

class PaymentsManager extends Payments
{
	/**
	 * Проверка является ли платеж продажей
	 * @param $iServID
	 * @param $iCUserID
	 * @param $payDate
	 * @return bool
	 * @throws NotFoundHttpException
	 */
	public static function isSale($iServID,$iCUserID,$payDate)
	{
		/** @var Services $obServ */
		$obServ = Services::find()->select(['id','c_inactivity'])->where(['id' => $iServID])->one();    //находим услугу
		if(empty($obServ) )
			throw new NotFoundHttpException('Service not found');

		if(empty($obServ->c_inactivity))    //не задан период бездействия, вернем FALSE
			return FALSE;

		$arCuser = self::getUserByGroup($iCUserID);

		$beginDate = CustomHelper::getBeginDayTime($payDate);   //время начала дня на момент платежа
		if(PaymentsSale::find()     //если были продажи похже даты платежа, то считаем что платеж не продажа
			->where(['cuser_id' => $arCuser,'service_id' => $iServID])
			->andWhere('sale_date >= :beginDate')
			->params([':beginDate' => $beginDate])
			->limit(1)
			->exists()
		)
			return FALSE;

		$beginDate = $beginDate - (int)$obServ->c_inactivity*86400*30;  //период бездействия = дата платежа минус время бездействия
		return  !Payments::find()   //проверяем , если не было платежей за период бехдействия, то счтаем платеж продажей
			->where(['cuser_id' => $arCuser,'serice_id' => $iServID])
			->andWhere('pay_date >= :beginDate')
			->params([':beginDate' => $beginDate])
			->limit(1)
			->exists();
	}

	public function getPaymentMonth($iServID,$iCUserID,$payDate,$useGroup = FALSE)
	{

		$arCuser = self::getUserByGroup($iCUserID);
		$obPaymentSale = PaymentsSale::find()
			->where(['cuser_id' => $arCuser,'service_id' => $iServID])
			->orderBy(['sale_date'])
		;








	}

	/**
	 * @param $iCUserID
	 * @return array
	 */
	protected static function getUserByGroup($iCUserID)
	{
		$arCuser = [$iCUserID];     //находим всех клиентов группы, если клиент в группе, иначе указывем только клиента
		$tmpGroup = CuserToGroup::find()
			->select(['group_id'])
			->where(['cuser_id' => $iCUserID])
			->all();

		$arGroup = [];
		foreach($tmpGroup as $t)
			$arGroup [] = $t->group_id;
		if(!empty($arGroup))
		{
			$tmp = CuserToGroup::find()
				->select(['cuser_id'])
				->where(['group_id' => $arGroup])
				->all();

			if(!empty($tmp))
				foreach($tmp as $t)
					$arCuser [] = $t->cuser_id;
		}
		return $arCuser;
	}

}