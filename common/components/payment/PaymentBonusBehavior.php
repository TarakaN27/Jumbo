<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 23.3.16
 * Time: 16.10
 */

namespace common\components\payment;


use common\components\helpers\CustomHelper;
use common\models\BonusScheme;
use common\models\BonusSchemeService;
use common\models\BonusSchemeServiceHistory;
use common\models\BonusSchemeToBuser;
use common\models\BonusSchemeToCuser;
use common\models\BUserBonus;
use common\models\CUserGroups;
use common\models\CuserToGroup;
use common\models\ExchangeCurrencyHistory;
use common\models\managers\PaymentsManager;
use common\models\PaymentsSale;
use common\models\Services;
use yii\base\Behavior;
use common\models\Payments;
use common\models\AbstractActiveRecord;
use backend\models\BUser;
use common\models\CUser;
use yii\db\Query;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use Yii;

class PaymentBonusBehavior extends Behavior
{
	protected
		$lastPayment = NULL;

	/**
	 * @return array
	 */
	public function events()
	{
		return [
			Payments::EVENT_AFTER_UPDATE => 'afterUpdate',
			Payments::EVENT_AFTER_INSERT => 'afterInsert',
			Payments::EVENT_AFTER_DELETE => 'afterDelete',
		];
	}

	/**
	 * @return bool
	 * @throws ServerErrorHttpException
	 */
	public function afterInsert()
	{
		/** @var Payments $model */
		$model = $this->owner;

		$iPayID = $model->id;             // ID платежа
		$iCUserID = $model->cuser_id;     // ID контрагента
		$sDate = $model->pay_date;        // Дата платежа
		$iService = $model->service_id;   // ID услуги

		$this->countingUnits($model,$iPayID,$iCUserID,$sDate,$iService);

		if($model->isSale && !empty($model->saleUser))  //если платеж является продажей
		{
			$this->saveSale($model);
			$this->countingSimpleBonus($model);
			$this->countingComplexBonus($model);
		}

		return TRUE;
	}

	/***
	 * Начисление юнитов
	 * @param $model
	 * @param $iPayID
	 * @param $iCUserID
	 * @param $sDate
	 * @param $iService
	 * @return bool
	 * @throws ServerErrorHttpException
	 */
	protected function countingUnits($model,$iPayID,$iCUserID,$sDate,$iService)
	{
		/** @var BUser $obManager */
		$obManager = BUser::find()      //находим менеджера, для которого зачисляется unit
		->select(['b.id','b.allow_unit'])
			->alias('b')
			->leftJoin(CUser::tableName().' as c','c.manager_id = b.id')
			->where(['c.id' => $iCUserID])
			->one();

		if(!$obManager || $obManager->allow_unit != AbstractActiveRecord::YES)  //проверяем нашли ли менеджера и разрешено ли менеджеру накапливать Units
			return FALSE;

		$obScheme = BonusScheme::find()  //получаем схему бонуса для пользователя.
			->joinWith('cuserID')
			->joinWith('usersID')
			->where([BonusScheme::tableName().'.type' => BonusScheme::TYPE_UNITS])
			->andWhere([BonusSchemeToBuser::tableName().'.buser_id' => $obManager->id])
			->orderBy(BonusSchemeToCuser::tableName().'.cuser_id IS NULL ASC , '.BonusScheme::tableName().'.updated_at DESC')
			->one();

		if(empty($obScheme))
			return FALSE;

		$obSchemeService = BonusSchemeServiceHistory::getCurrentBonusService($model->pay_date,$iService,$obScheme->id); //находим параметры по схеме
		/** @var BonusScheme $obScheme */
		if(!$obSchemeService || !is_object( $obScheme = $obSchemeService->scheme))
			return FALSE;

		if($obSchemeService->unit_multiple) //если зачисляется юнит по каждому платежу
		{
			$this->addBonus($obManager->id,$iPayID,$obSchemeService->scheme_id,$iService,$iCUserID,$obSchemeService->cost);
		}else{
			//необходимо проверить были ли бонусы по данной услуге уже зачисленны
			$beginMonth = CustomHelper::getBeginMonthTime($model->pay_date);    //время начала месяца, когда был совершен платеж
			$endMonth = CustomHelper::getEndMonthTime($model->pay_date);    //окончание месяца

			$obBonus = BUserBonus::find()
				->alias('bb')
				->joinWith('payment p')
				->joinWith('scheme sc');

			$arCuser = [$iCUserID];
			if($obScheme->grouping_type == BonusScheme::GROUP_BY_CMP_GROUP)
			{
				$groupIdsTmp = CuserToGroup::find()->select(['group_id'])->where(['cuser_id' => $iCUserID])->all();
				$groupIds = [];
				foreach($groupIdsTmp as $group)
					$groupIds [] = $group->group_id;

				if(!empty($groupIds)) {
					$tmp = (new Query())
						->select(['cuser_id'])
						->from(CuserToGroup::tableName())
						->where(['group_id' => $groupIds])
						->all();
					if(!empty($tmp))
						foreach($tmp as $t)
							$arCuser[] = $t['cuser_id'];
				}
				$arCuser = array_unique($arCuser);
				$obBonus->joinWith('cuser.cmpGroup');
			}

			$obBonus->where(['bb.cuser_id' => $arCuser,'sc.type' => BonusScheme::TYPE_UNITS])
				->andWhere('p.pay_date >= :beginDate AND p.pay_date <=:endDate')
				->andWhere(['bb.service_id' => $iService])
				->params([':beginDate' => $beginMonth,':endDate' => $endMonth]);

			if($obBonus->exists())
				return FALSE;

			$this->addBonus($obManager->id,$iPayID,$obSchemeService->scheme_id,$iService,$iCUserID,$obSchemeService->cost);

		}

		return TRUE;
	}


	/**
	 * Сохраняем продажу
	 * @param Payments $model
	 * @param bool|FALSE $simpleBonus
	 * @return bool|PaymentsSale|null
	 * @throws ServerErrorHttpException
	 */
	protected function saveSale(Payments $model,$simpleBonus = FALSE)
	{
		if(empty($model->saleUser))
			return FALSE;

		$inActivePeriod = (int)Yii::$app->config->get('c_inactivity',0);  //период бездействия в месяцах

		if($inActivePeriod <= 0)    //не задан период бездействия, вернем FALSE
			return FALSE;

		$lastPayment = Payments::find() //ищем последнюю оплату по клиенту(если не было, значит продажа)
			->select(['pay_date'])
			->where(['cuser_id' => $model->cuser_id])
			->andWhere('id != :ID and pay_date <= :payDate');
		//if($simpleBonus)
		//	$lastPayment->andWhere(['service_id' => $model->service_id]);   //для простого бонуса смотрим продажи по услугам

		$lastPayment = $lastPayment->params([
			':ID' => $model->id,
			':payDate' => $model->pay_date
		])
			->orderBy(['pay_date' => SORT_DESC])
			->one();

		$obSale = NULL;
		if(empty($lastPayment)) //если нет оплат, то добавляем продажу
		{
			$obSale = new PaymentsSale([
				'cuser_id' => $model->cuser_id,
				'service_id' => $model->service_id,
				'buser_id' => $model->saleUser,
				'sale_date' => $model->pay_date,
				'sale_num' => 1
			]);
			if(!$obSale->save())
				throw new ServerErrorHttpException();
		}else{
			$beginDate = CustomHelper::getDateMinusNumMonth(time(),$inActivePeriod);    //отнимаем от текущей даты период бездействия
			$obLastSale = PaymentsSale::find()  //ищем последнюю продажу, для получения порядкового номера продажи
				->where([
					'cuser_id' => $model->cuser_id,
				])
				->andWhere('sale_date <= :saleDate')
				->orderBy(['sale_num' => SORT_DESC])
				->params([
					':saleDate' => $model->pay_date
				])
				->one();

			if(empty($obLastSale)) //если продаж не было
			{
				$obSale = new PaymentsSale([
					'cuser_id' => $model->cuser_id,
					'service_id' => $model->service_id,
					'buser_id' => $model->saleUser,
					'sale_date' => $model->pay_date,
					'sale_num' => 1
				]);
			}else
			if($beginDate > $lastPayment->pay_date){
				$obSale = new PaymentsSale([
					'cuser_id' => $model->cuser_id,
					'service_id' => $model->service_id,
					'buser_id' => $model->saleUser,
					'sale_date' => $model->pay_date,
					'sale_num' => $obLastSale->sale_num++
				]);
			}else{
				$obSale = new PaymentsSale([
					'cuser_id' => $model->cuser_id,
					'service_id' => $model->service_id,
					'buser_id' => $model->saleUser,
					'sale_date' => $model->pay_date,
					'sale_num' => $obLastSale->sale_num
				]);
			}

			if(!$obSale->save())
				throw new ServerErrorHttpException();
		}

		return $obSale;
	}

	/**
	 * Простой бонус
	 * @param Payments $model
	 * @return bool
	 * @throws ServerErrorHttpException
	 */
	protected function countingSimpleBonus(Payments $model)
	{
		$obScheme = BonusScheme::find()  //получаем схему бонуса для пользователя.
			->joinWith('cuserID')
			->joinWith('usersID')
			->where([BonusScheme::tableName().'.type' => BonusScheme::TYPE_SIMPLE_BONUS])
			->andWhere([BonusSchemeToBuser::tableName().'.buser_id' => $model->saleUser])
			->orderBy([
				BonusSchemeToCuser::tableName().'.cuser_id IS NULL' => SORT_ASC,
				BonusScheme::tableName().'.updated_at' => SORT_DESC
			])
			->one();

		if(empty($obScheme))
			return FALSE;

		$obBServ = BonusSchemeServiceHistory::getCurrentBonusService($model->pay_date,$model->service_id,$obScheme->id);    //получаем параметры схемы

		if(empty($obBServ))
			return FALSE;

		$excRate = ExchangeCurrencyHistory::getCurrencyInBURForDate($model->pay_date,$model->currency_id);  //получаем курс валюты в бел. рублях
		$amount = $excRate*$model->pay_summ;    //получаем сумму платежа в бел. рублях

		if(is_array($obBServ->legal_person) &&  //проверяем не указано ли для Юр. лица отнимать НАЛОГ от платежа
			isset($obBServ->legal_person[$model->legal_id]) &&
			$obBServ->legal_person[$model->legal_id] == 1)
		{
			$amount = $amount - CustomHelper::getVatMountByAmount($amount); //отнимем от суммы платежа налог
		}

		return $this->addBonus($model->saleUser,$model->id,$obScheme->id,$model->service_id,$model->cuser_id,$amount);  //добавим бонус
	}

	/**
	 * Составной бонус
	 * @param $model
	 * @return bool
	 * @throws ServerErrorHttpException
	 */
	protected function countingComplexBonus($model)
	{
		$arCuserGroup = PaymentsManager::getUserByGroup($model->cuser_id);  //получаем пользователей группы
		$inActivePeriod = (int)Yii::$app->config->get('c_inactivity',0);  //период бездействия в месяцах

		$saleUser = NULL;   //определеяем кому начислять бонус
		$obSale = PaymentsSale::find()
			->where(['cuser_id' => $arCuserGroup])
			->orderBy(['sale_date' => SORT_ASC,'id' => SORT_ASC])
			->one();

		if(empty($obSale))  //есил не было продажи, то бонус начисляется текущему продашему
		{
			$saleUser = $model->saleUser;
		}else{
			$obLast = Payments::find() //ищем последнюю оплату по клиенту(если не было, значит продажа) за период бездействия
				->select(['pay_date'])
				->where(['cuser_id' => $arCuserGroup])
				->andWhere('id != :ID and pay_date >= :payDate')
				->params([
					':ID' => $model->id,
					':payDate' => CustomHelper::getDateMinusNumMonth($model->pay_date,$inActivePeriod)
				])
				->orderBy(['pay_date' => SORT_DESC])
				->one();
			if(empty($obLast))
				$saleUser = $model->saleUser;
			else
				$saleUser = $obSale->buser_id;
		}

		$obScheme = BonusScheme::find()  //получаем схему бонуса для пользователя.
			->joinWith('cuserID')
			->joinWith('usersID')
			->where([BonusScheme::tableName().'.type' => BonusScheme::TYPE_COMPLEX_TYPE])
			->andWhere([BonusSchemeToBuser::tableName().'.buser_id' => $saleUser])
			->orderBy([BonusSchemeToCuser::tableName().'.cuser_id IS NULL' => SORT_ASC,BonusScheme::tableName().'.updated_at' => SORT_DESC])
			->one();

		if(empty($obScheme))
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

		$excRate = ExchangeCurrencyHistory::getCurrencyInBURForDate($model->pay_date,$model->currency_id);  //курс валюты в бел рублях
		$amount = $excRate*$model->pay_summ*($percent/100); //сумма платежа
		if(is_array($obBServ->legal_person) &&  //если ля юр. лица указан, что нужно отнять налог
			isset($obBServ->legal_person[$model->legal_id]) &&
			$obBServ->legal_person[$model->legal_id] == 1)
		{
			$amount = $amount - CustomHelper::getVatMountByAmount($amount);	  //отнимаем налог
		}

		return $this->addBonus($model->saleUser,$model->id,$obScheme->id,$model->service_id,$model->cuser_id,$amount);  //добавляем бонус
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
	protected function addBonus($iUserID,$iPaymentID,$iSchemeID,$iServiceID,$iCuserID,$amount)
	{
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

	/**
	 * @return bool
	 */
	public function afterDelete()
	{
		BUserBonus::deleteAll(['payment_id' => $this->owner->id]);
		return TRUE;
	}

	/**
	 * @throws ServerErrorHttpException
	 */
	public function actionAfterUpdate()
	{
		//удаляем старые рассчеты
		BUserBonus::deleteAll(['payment_id' => $this->owner->id]);

		//считаем заново
		/** @var Payments $model */
		$model = $this->owner;

		$iPayID = $model->id;             // ID платежа
		$iCUserID = $model->cuser_id;     // ID контрагента
		$sDate = $model->pay_date;        // Дата платежа
		$iService = $model->service_id;   // ID услуги

		$this->countingUnits($model,$iPayID,$iCUserID,$sDate,$iService);

		if($model->isSale && !empty($model->saleUser))  //если платеж продажа
		{
			$this->saveSale($model);
			$this->countingSimpleBonus($model);
			$this->countingComplexBonus($model);
		}
	}


}