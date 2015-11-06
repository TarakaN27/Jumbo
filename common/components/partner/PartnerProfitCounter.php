<?php
namespace common\components\partner;
use common\models\Acts;
use common\models\PartnerCondition;
use common\models\PartnerCuserServ;
use common\models\PartnerProfit;
use common\models\PartnerPurse;

/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 5.11.15
 * Time: 12.53
 */
class PartnerProfitCounter
{
	CONST
		ACT_PERIOD = 12; // период за который считаем акты, для выбора условия

	protected
		$_error = [],
		$_act = NULL,   //акты по котору необходимо начислить прибыль
		$_notifData = [], //данные для извещения
		$_sendNotification = FALSE; // отсылать уведомление

	/**
	 * @param Acts $act
	 * @param bool|FALSE $sendNotification
	 */
	public function __construct(Acts $act,$sendNotification = FALSE)
	{
		$this->_act = $act;
		$this->_sendNotification = $sendNotification;
	}

	/**
	 *
	 */
	public function __destruct()
	{
		if($this->_sendNotification)
			$this->sendNotification();
	}

	/**
	 * при удалении акта, необходимо скорректировать прибыль партнера
	 * @return bool
	 * @throws \Exception
	 * @throws \yii\db\Exception
	 */
	public function deleteProfit($oldProfit = NULL,$partnerID = NULL)
	{
		/** @var PartnerProfit $obProfit */
		$obProfit = PartnerProfit::find()->where(['act_id' => $this->_act->id])->one();
		if(!$obProfit)
			if(!empty($oldProfit) && !empty($partnerID))
				return $this->deleteProfitHelper($oldProfit,$partnerID);
			else
				return TRUE;

		$tr = \Yii::$app->db->beginTransaction();
		if(!$this->deleteProfit($obProfit->amount,$obProfit->partner_id))
			return $this->addError('Can not save purse');

		if($obProfit->delete())
		{
			$tr->commit();
			return TRUE;
		}else{
			$tr->rollBack();
			return FALSE;
		}
	}

	/**
	 * @param $amount
	 * @param $partnerID
	 * @return bool
	 */
	protected function deleteProfitHelper($amount,$partnerID)
	{
		$obPurse = PartnerPurse::find()->where(['partner_id' => $partnerID])->one();
		if(!$obPurse)
			return $this->addError('Purse not found');
		$obPurse->amount-= $amount;

		return $obPurse->save();
	}

	public function updateProfit($oldAmount = NULL)
	{
		$obPartn = $this->getPartner();
		if(!$obPartn)
		{
			return FALSE; // нет патнера, которому необходимо начислить прибыль
		}
		/** @var PartnerProfit $obOldProfit */
		$obOldProfit = PartnerProfit::find()->where(['act_id' => $this->_act->id])->one();  //получаем старую прибыль
		if(!$obOldProfit)   //если нет прибыли, то добавим
			return $this->countProfitPerPeriod();


		//получаем сумму актов за предыдущий период
		$actsAmount = $this->getActsAmountByPeriod($this->_act->cuser_id,$obPartn->connect,$this->_act->act_date);

		//если нет актов, то условие будем искать по сумме текущего акта
		$aAmount = empty($actsAmount) ? $this->_act->amount : $actsAmount;
		unset($actsAmount);
		/** @var PartnerCondition $obCond */
		$obCond = $this->getCondition($aAmount);
		if(!$obCond)
			return $this->addError('Condition not found');

		//считаем прибыль
		$iProfit = $this->countProfit($this->_act->amount,$obCond->percent);

		//проверяем отличаются ли старые и новые рассчеты
		if($iProfit == $obOldProfit->amount && $obOldProfit->percent == $obCond->percent)
			return TRUE;

		//получаем кошелек
		$obPurse = PartnerPurse::getPurseNotCached($obPartn->partner_id);
		if(!$obPurse)
			return $this->addError('Purse not found');

		return $this->updatePartnerProfit($obOldProfit,$obPurse,$obCond,$iProfit,$oldAmount);
	}


	/**
	 * Обновляем кошелек
	 * @param PartnerProfit $obOldProfit
	 * @param PartnerPurse $obPurse
	 * @param PartnerCondition $obCond
	 * @param $profit
	 * @param $oldAmount
	 * @return bool
	 * @throws \yii\db\Exception
	 */
	protected function updatePartnerProfit(
		PartnerProfit $obOldProfit,
		PartnerPurse $obPurse,
		PartnerCondition $obCond,
		$profit,$oldAmount)
	{
		$diff = $profit - $obOldProfit->amount; //разница в прибыли
		$diffActAmount = $this->_act->amount - $oldAmount; //разница в актах
		$tr = \Yii::$app->db->beginTransaction();
		$obOldProfit->amount = $profit;
		$obOldProfit->percent = $obCond->percent;

		if(!$obOldProfit->save()) // пробуем сохранить
		{
			//$tr->rollBack(); //все плохо. откат
			return $this->addError('Can not add profit');
		}

		$obPurse->amount +=$profit; // суммируем прибыль
		$obPurse->acts += $diffActAmount;  //суммирем общую сумму прибыли

		if(!$obPurse->save()) {//пробуем сохранить кашелек
			$tr->rollBack(); // все плохо. откат
			return $this->addError('Can not add profit to partner purse');
		}
		$this->_notifData = ['Partner' => $obPurse->partner_id,'Profit' => $profit];
		$tr->commit(); //все отлично. коммитим.

		return TRUE;
	}

	/**
	 * @return bool
	 */
	public function countProfitPerPeriod()
	{
		$obPartn = $this->getPartner();
		if(!$obPartn)
		{
			return FALSE; // нет патнера, которому необходимо начислить прибыль
		}

		//получаем сумму актов за предыдущий период
		$actsAmount = $this->getActsAmountByPeriod($this->_act->cuser_id,$obPartn->connect,$this->_act->act_date);

		//если нет актов, то условие будем искать по сумме текущего акта
		$aAmount = empty($actsAmount) ? $this->_act->amount : $actsAmount;
		unset($actsAmount);
		/** @var PartnerCondition $obCond */
		$obCond = $this->getCondition($aAmount);
		if(!$obCond)
			return $this->addError('Condition not found');

		//считаем прибыль
		$iProfit = $this->countProfit($this->_act->amount,$obCond->percent);

		//получаем кошелек
		$obPurse = PartnerPurse::getPurseNotCached($obPartn->partner_id);
		if(!$obPurse)
			return $this->addError('Purse not found');

		return $this->saveProfit($obPurse,$obCond,$iProfit);
	}

	/**
	 * Сохраняем все что насчитали
	 * @param PartnerPurse $obPurse
	 * @param PartnerCondition $obCond
	 * @param $profit
	 * @return bool
	 */
	protected function saveProfit(PartnerPurse $obPurse, PartnerCondition $obCond,$profit)
	{
		$tr = \Yii::$app->db->beginTransaction();   //Стартуем транзакцию
		$obProfit = new PartnerProfit([ // сохраняем историю прибыли для партнера
			'partner_id' => $obPurse->partner_id,
			'act_id' => $this->_act->id,
			'cond_id' => $obCond->id,
			'amount' => $profit,
			'percent' => $obCond->percent,
		]);

		if(!$obProfit->save()) // пробуем сохранить
		{
			//$tr->rollBack(); //все плохо. откат
			return $this->addError('Can not add profit');
		}

		$obPurse->amount +=$profit; // суммируем прибыль
		$obPurse->acts += $this->_act->amount;  //суммирем общую сумму прибыли

		if(!$obPurse->save()) {//пробуем сохранить кашелек
			$tr->rollBack(); // все плохо. откат
			return $this->addError('Can not add profit to partner purse');
		}
		$this->_notifData = ['Partner' => $obPurse->partner_id,'Profit' => $profit];
		$tr->commit(); //все отлично. коммитим.
		return TRUE;
	}

	/**
	 * Расчет прибыли партнера по акту
	 * @param $amount
	 * @param $percent
	 * @return float
	 */
	protected function countProfit($amount,$percent)
	{
		return round($percent/100*$amount);
	}

	/**
	 * Получаем условие по сумме.
	 * @param $actsAmount
	 * @return mixed
	 */
	protected function getCondition($actsAmount)
	{
		return PartnerCondition::find()
			->where('min_amount >= :amount && max_amount <= :amount',[':amount' => $actsAmount])
			->orderBy('id DESC')
			->one();
	}

	/**
	 * Получаем акты контрагента за период startDate -- endDate
	 * @param $cuserID
	 * @param $startDate
	 * @param $endDate
	 * @return mixed
	 */
	protected function getActsAmountByPeriod($cuserID,$startDate,$endDate)
	{
		$date = new \DateTime($endDate);
		$date->modify('-1 month');
		$end = $date->format('Y-t-d'); //конец прошлого месяца
		$date->modify('-'.self::ACT_PERIOD.' month');
		$start = $date->format('Y-m-d');//- 12 месяцев

		return Acts::find()
			->select('SUM(amount) as amount')
			->where(['cuser_id' => $cuserID])
			->andWhere(' act_date >= :start_date AND act_date <= :end_date  AND act_date > = :start',[
				':start_date' => $startDate, //ограничиваемся датой привязки услуги в партнеру
				':end_date' => $end, //ограничиваемся датой окончания прошлого месяца
				':start' => $start //ограничиваем 12 месяцами
			])
			->scalar();
	}

	/**
	 * Получаем связь партнера(партнер-контрагент-услуга-время_привзяки)
	 * Время привязки -- акты ,которые добавлены раньше этого времени не учитываем.
	 * Так как это было до дого как пользователь стал партнером.
	 * @return null|static
	 */
	protected function getPartner()
	{
		return PartnerCuserServ::find()
			->where([
				'cuser_id' => $this->_act->cuser_id,
				'service_id' => $this->_act->service_id,
			])
			->andWhere(' connect < :data ',[':data' => $this->_act->act_date])
			->one();
	}

	/**
	 * @param $str
	 * @return bool
	 */
	protected function addError($str)
	{
		$this->_error [] = $str;
		return FALSE;
	}

	/**
	 * @return bool
	 */
	protected function hasError()
	{
		return !empty($this->_error);
	}

	/**
	 * @return bool
	 */
	protected function sendNotification()
	{
		$arData = $this->_notifData;
		$arData['Act'] = $this->_act->id;
		$arData['Date'] = \Yii::$app->formatter->asDatetime('NOW');
		$sErr = $this->hasError() ? 'Error .' : '';
		\Yii::$app->mailer->compose('partnerProfit', ['arData' => $arData])
			->setFrom([\Yii::$app->params['supportEmail'] => \Yii::$app->name])
			->setTo(\Yii::$app->params['adminEmail'])
			->setSubject($sErr.'Partner profit  '.$arData['Date'] . \Yii::$app->name)
			->send();

		return TRUE;
	}
}