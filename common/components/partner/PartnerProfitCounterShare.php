<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 6.11.15
 * Time: 14.06
 */

namespace common\components\partner;


use common\models\Acts;
use common\models\PartnerCondition;
use common\models\PartnerProfit;
use common\models\PartnerPurse;

class PartnerProfitCounterShare
{
	/**
	 * @param $pID
	 * @param $cID
	 * @param $servID
	 * @param $date
	 * @return bool
	 */
	public function countingProfitForPartner($pID,$cID,$servID,$date)
	{
		$arActs = $this->getActs($cID,$servID,$date);
		if(empty($arActs))
			return TRUE; //нечего добавлять

		$minMax = $this->getMinMaxAMount($arActs); // найдем границы для условий
		if(is_null($minMax['min']) || is_null($minMax['max']) || is_null($minMax['date']))
			return FALSE;

		$arCond = $this->getConditions($minMax['min'],$minMax['max'],$minMax['date']);

		$bSuccess = TRUE;
		foreach($arActs as $act)
		{
			if(!$this->countingProfitHelper($pID,$arCond,$act) && $bSuccess)
				$bSuccess = FALSE;
		}
		return $bSuccess;
	}

	/**
	 * @param $pID
	 * @param $arCond
	 * @param $act
	 * @return bool
	 */
	protected function countingProfitHelper($pID,$arCond,$act)
	{
		$obCond = $this->getConditionHelper($arCond,$act->amount,$act->act_date);
		if(is_null($obCond))
			return FALSE;

		$iProfit = $this->countProfit($act->amount,$obCond->percent);

		$obPurse = PartnerPurse::getPurseNotCached($pID);
		if(!$obPurse)
			return FALSE;

		return $this->saveProfit($obPurse,$obCond,$act,$iProfit);
	}

	/**
	 * @todo вынести в общий метод
	 * @param PartnerPurse $obPurse
	 * @param PartnerCondition $obCond
	 * @param Acts $obAct
	 * @param $profit
	 * @return bool
	 * @throws \yii\db\Exception
	 */
	protected function saveProfit(PartnerPurse $obPurse, PartnerCondition $obCond,ACts $obAct,$profit)
	{
		$tr = \Yii::$app->db->beginTransaction();   //Стартуем транзакцию
		$obProfit = new PartnerProfit([ // сохраняем историю прибыли для партнера
			'partner_id' => $obPurse->partner_id,
			'act_id' => $obAct->id,
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
		$obPurse->acts += $obAct->amount;  //суммирем общую сумму прибыли

		if(!$obPurse->save()) {//пробуем сохранить кашелек
			$tr->rollBack(); // все плохо. откат
			return $this->addError('Can not add profit to partner purse');
		}
		$tr->commit(); //все отлично. коммитим.
		return TRUE;
	}

	/**
	 * @todo вынести в общий метод
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
	 * @param $arCond
	 * @param $amount
	 * @param $actDate
	 * @return array
	 */
	protected function getConditionHelper($arCond,$amount,$actDate)
	{
		$arItem = []; //собираем все уловия подходящие под сумму amount и дату акта
		foreach($arCond as $cond)
		{
			if($cond->min_amount<= $amount && $amount <= $cond->max_amount && $cond->start_date <= $actDate)
				$arItem[] = $cond;
		}

		if(empty($arItem)) //если нет условия
			return NULL;

		if(count($arItem) == 1) //всего одно условие
			return $arItem[0]->id;

		$tmpItem = NULL;
		foreach($arItem as $item) //нашли несколько уловий, выбираем самое позднее. Проверяем по ID.
		{
			if(is_null($tmpItem))
				$tmpItem = $item;
			elseif($tmpItem->id < $item->id)
				$tmpItem = $item->id;
		}
		return $tmpItem;
	}

	/**
	 * Выбираем условия, которые подходят под границы
	 * @param $min
	 * @param $max
	 * @param $endDate
	 * @return mixed
	 */
	protected function getConditions($min,$max,$endDate)
	{
		return PartnerCondition::find()
			->where('min_amount <= :max_amount AND max_amount >= :min_amount AND start_date <= :start_date',[
				':max_amount' => $max,
				':min_amount' => $min,
				':start_date' => $endDate
			])
			->all();
	}

	/**
	 * Получаем границы для подбора условий.
	 * @param array $arActs
	 * @return array
	 */
	protected function getMinMaxAMount(array $arActs)
	{
		$min = NULL;
		$max = NULL;
		$date = NULL;
		foreach($arActs as $act)
		{
			if(is_null($min))
				$min = $act->amount;
			else
				$min = $min > $act->amount ? $act->amount : $min;

			if(is_null($max))
				$max = $act->amount;
			else
				$max = $max > $act->amount ? $max : $act->amount;

			if(is_null($date))
				$date = $act->act_date;
			else
				$date = $act->act_date > $date ? $act->act_date : $date;
		}

		return ['min' => $min,'max' => $max];
	}


	/**
	 * Получаем акты
	 * @param $cID
	 * @param $servID
	 * @param $date
	 * @return array
	 */
	protected function getActs($cID,$servID,$date)
	{
		return Acts::find()
			->leftJoin(PartnerProfit::tableName().' pp',['pp.act_id' => Acts::tableName().'.id'])
			->where(['cuser_id' => $cID,'service_id' => $servID])
			->andWhere('pp.act_id is NULL')
			->andWhere('act_date >= :date',[':date' => $date])
			->all();
	}
}