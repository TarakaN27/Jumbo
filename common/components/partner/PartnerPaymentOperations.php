<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 12.11.15
 * Time: 11.27
 */

namespace common\components\partner;


use common\models\PartnerPurse;
use common\models\Payments;
use common\models\PartnerCuserServ;

class PartnerPaymentOperations
{

	protected
		$_payment;

	/**
	 * @param Payments $payment
	 */
	public function __construct(Payments $payment)
	{
		$this->_payment = $payment;
	}

	/**
	 * @return bool
	 */
	public function paymentInsert()
	{
		$obPrt = $this->getPartner();
		if(!$obPrt)
			return TRUE;

		/** @var PartnerPurse $obPurse */
		$obPurse = PartnerPurse::getPurseNotCached($obPrt->id);
		if(!$obPurse)
			return FALSE;

		$obPurse->payments+=$this->_payment->pay_summ;
		return $obPurse->save();
	}

	/**
	 * @return bool
	 */
	public function paymentDelete($date =NULL,$serviceID=NULL,$userID=NULL,$oldAmount = NULL)
	{
		$obPrt = $this->getPartner($date,$serviceID,$userID);
		if(!$obPrt)
			return TRUE;

		/** @var PartnerPurse $obPurse */
		$obPurse = PartnerPurse::getPurseNotCached($obPrt->id);
		if(!$obPurse)
			return FALSE;
		$amount = is_null($oldAmount) ? $this->_payment->pay_summ : $oldAmount;
		$obPurse->payments-=$amount;
		return $obPurse->save();
	}

	/**
	 * @param $oldAmount
	 * @return bool
	 */
	public function paymentUpdate($oldAmount,$oldDate = NULL,$oldService = NULL)
	{
		/** @var PartnerCuserServ $obPrt */
		$obPrt = $this->getPartner($oldDate,$oldService);   //получаем партенра
		if(!$obPrt) //если партенра нет по старым данным
			if(!is_null($oldDate) || !is_null($oldService))
				return $this->paymentInsert(); //проверяем можем ил что-нибудь начислить по новым
			else
				return TRUE;

		$bDecrFlag = FALSE; // нужно проверить , если старый партнер был, будет ли существовать и новый
		if(!is_null($oldService)) {
			$obPrtNew = $this->getPartner();
			if(!$obPrtNew)
				$bDecrFlag = TRUE;  //если нет, то ставим флаг, чтобы удалить сумму
		}

		/** @var PartnerPurse $obPurse */
		$obPurse = PartnerPurse::getPurseNotCached($obPrt->id);
		if(!$obPurse)
			return FALSE;

		if($bDecrFlag || (!is_null($oldDate) && $obPrt->connect > date('Y-m-d',$this->_payment->pay_date))) //если изменилась дата, и дата не попадает под условие
		{
			$obPurse->payments-=$oldAmount; // отнимем платеж
		}else{
			$diff = $this->_payment->pay_summ - $oldAmount;
			$obPurse->payments-=$diff;
		}

		return $obPurse->save();
	}

	/**
	 * Если обновили пользователя
	 * @param $date
	 * @param $serviceID
	 * @param $userID
	 * @param $oldAmount
	 * @return bool
	 */
	public function paymentUpdateUser($date,$serviceID,$userID,$oldAmount)
	{
		$this->paymentDelete($date,$serviceID,$userID,$oldAmount);  //удаляем все по старому
		return $this->paymentInsert();  //добавляем все по новому
	}

	/**
	 * @param null $date
	 * @return mixed
	 */
	protected function getPartner($date = NULL,$serviceID = NULL,$userID = NULL)
	{

		$date = is_null($date) ? $this->_payment->pay_date : $date;
		$serviceID = is_null($serviceID) ? $this->_payment->service_id : $serviceID;
		$userID = is_null($userID) ? $this->_payment->cuser_id : $userID;
		return PartnerCuserServ::find()
			->where([
				'cuser_id' => $userID,
				'service_id' => $serviceID,
			])
			->andWhere(' connect < :data ',[':data' => date('Y-m-d',$date)])
			->one();
	}
}