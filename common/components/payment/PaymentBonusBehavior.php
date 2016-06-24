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
use common\models\BonusSchemeExceptCuser;
use common\models\BonusSchemeService;
use common\models\BonusSchemeServiceHistory;
use common\models\BonusSchemeToBuser;
use common\models\BonusSchemeToCuser;
use common\models\BUserBonus;
use common\models\CUserGroups;
use common\models\CuserToGroup;
use common\models\ExchangeCurrencyHistory;
use common\models\managers\PaymentsManager;
use common\models\PartnerCuserServ;
use common\models\PaymentCondition;
use common\models\PaymentRequest;
use common\models\PaymentsCalculations;
use common\models\PaymentsSale;
use common\models\Services;
use yii\base\Behavior;
use common\models\Payments;
use common\models\AbstractActiveRecord;
use backend\models\BUser;
use common\models\CUser;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\web\NotAcceptableHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use Yii;

class PaymentBonusBehavior extends Behavior
{
	protected
		$onlyForId = NULL, 				//id пользователя для бонусов(если нужно сделать перерасчет для определенного пользователя, то указываем его id)
		$lastPayment = NULL;

	/**
	 * @param $iUserId
	 */
	public function setOnlyForIdUser($iUserId)
	{
		$this->onlyForId = $iUserId;
	}

	/**
	 * @return array
	 */
	public function events()
	{
		return [
			Payments::EVENT_AFTER_UPDATE => 'afterUpdate',
			Payments::EVENT_SAVE_DONE => 'afterInsert',
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
		$this->countingPartnerBonus($model);

		if($model->isSale && !empty($model->saleUser))  //если платеж является продажей
		{
			$this->saveSale($model);
			$this->countingSimpleBonus($model);
			$this->countingComplexBonus($model);
		}else{
			$this->countingSimpleBonus($model,BonusScheme::BASE_PAYMENT);
			$this->countingComplexBonus($model,BonusScheme::BASE_SALE);
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
			->leftJoin(PaymentRequest::tableName().' as r','r.manager_id = b.id')
			->where(['r.id' => $model->prequest_id])
			->one();

		if(!$obManager || $obManager->allow_unit != AbstractActiveRecord::YES)  //проверяем нашли ли менеджера и разрешено ли менеджеру накапливать Units
			return FALSE;

		$arExcept = BonusSchemeExceptCuser::getExceptSchemesForCuser([$iCUserID]);	//сземы искллючения для пользователя

		$obScheme = BonusScheme::find()  //получаем схему бонуса для пользователя.
			->alias('bs')
			->joinWith('cuserID cid')
			->joinWith('usersID uid')
			->joinWith('exceptCusers excu')
			->where(['bs.type' => BonusScheme::TYPE_UNITS])
			->andWhere(['uid.buser_id' => $obManager->id]);

			if($arExcept)
				$obScheme->andWhere(['NOT IN','bs.id',$arExcept]);

			$obScheme = $obScheme->orderBy('`cid`.`cuser_id` IS NULL ASC , `bs`.`updated_at` DESC')
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
				'payment_id' => $model->id,
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
					'payment_id' => $model->id,
					'sale_num' => 1
				]);
			}else
			if($beginDate > $lastPayment->pay_date){
				$obSale = new PaymentsSale([
					'cuser_id' => $model->cuser_id,
					'service_id' => $model->service_id,
					'buser_id' => $model->saleUser,
					'sale_date' => $model->pay_date,
					'payment_id' => $model->id,
					'sale_num' => $obLastSale->sale_num++
				]);
			}else{
				$obSale = new PaymentsSale([
					'cuser_id' => $model->cuser_id,
					'service_id' => $model->service_id,
					'buser_id' => $model->saleUser,
					'sale_date' => $model->pay_date,
					'payment_id' => $model->id,
					'sale_num' => $obLastSale->sale_num
				]);
			}

			if(!$obSale->save())
				throw new ServerErrorHttpException();
		}

		return $obSale;
	}

	/**
	 * @param Payments $model
	 * @param int $paymentBase
	 * @return bool
	 * @throws NotFoundHttpException
	 * @throws ServerErrorHttpException
	 */
	public function countingSimpleBonus(Payments $model,$paymentBase = BonusScheme::BASE_SALE)
	{
		if($paymentBase == BonusScheme::BASE_PAYMENT)
		{
			$obManager = BUser::find()      //находим менеджера
			->select(['b.id','b.allow_unit'])
				->alias('b')
				->leftJoin(PaymentRequest::tableName().' as r','r.manager_id = b.id')
				->where(['r.id' => $model->prequest_id])
				->one();

			if(!$obManager )  //проверяем нашли ли менеджера
				return FALSE;

			$saleUser = $obManager->id;
		}else{
			$saleUser = $model->saleUser;
		}

		if(!is_null($this->onlyForId) && $this->onlyForId != $saleUser)
			return false;

		$arExcept = BonusSchemeExceptCuser::getExceptSchemesForCuser([$model->cuser_id]);	//схемы искллючения для пользователя
		if($paymentBase == BonusScheme::BASE_SALE)
			$paymentBase = [BonusScheme::BASE_SALE,BonusScheme::BASE_PAYMENT];
		$obScheme = BonusScheme::find()  //получаем схему бонуса для пользователя с заднной компанией.
				->joinWith('cuserID')
				->joinWith('usersID')
				->where([
					BonusScheme::tableName().'.type' => BonusScheme::TYPE_SIMPLE_BONUS,
					BonusSchemeToBuser::tableName().'.buser_id' => $saleUser,
					BonusSchemeToCuser::tableName().'.cuser_id' => $model->cuser_id,
					'payment_base' => $paymentBase
				]);
				if(!empty($arExcept))
					$obScheme->andWhere(['NOT IN',BonusScheme::tableName().'.id',$arExcept]);

				$obScheme = $obScheme->orderBy([
					BonusScheme::tableName().'.updated_at' => SORT_DESC
				])
				->one();
		if(!$obScheme) {
			$obScheme = BonusScheme::find()//получаем схему бонуса для пользователя.
			->joinWith('cuserID')
				->joinWith('usersID')
				->joinWith('exceptCusers')
				->where([
					BonusScheme::tableName() . '.type' => BonusScheme::TYPE_SIMPLE_BONUS,
					BonusSchemeToBuser::tableName() . '.buser_id' => $saleUser,
					'payment_base' => $paymentBase
				])
				->andWhere(BonusSchemeToCuser::tableName() . '.scheme_id IS NULL');
			if (!empty($arExcept))
				$obScheme->andWhere(['NOT IN', BonusScheme::tableName().'.id', $arExcept]);

			$obScheme = $obScheme->orderBy([
				BonusScheme::tableName() . '.updated_at' => SORT_DESC
			])
				->one();
		}

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
			isset($obBServ->legal_person[$model->legal_id]['deduct']) &&
			isset($obBServ->legal_person[$model->legal_id]['deduct']) == 1)
		{
			$obCuser = CUser::find()->select(['id','is_resident'])->where(['id' => $model->cuser_id])->one();	//пользователь
			if(!$obCuser)
				throw  new NotFoundHttpException();

			$key = $obCuser->is_resident ? 'res' : 'not_res';
			if(isset($obBServ->legal_person[$model->legal_id][$key]))
			{
				$tax = NULL;
				if(isset($obBServ->legal_person[$model->legal_id][$key.'_tax']) && is_numeric($obBServ->legal_person[$model->legal_id][$key.'_tax']))
					$tax = $obBServ->legal_person[$model->legal_id][$key.'_tax'];

				$amount = CustomHelper::getVatMountByAmount($amount,$tax); //отнимем от суммы платежа налог
			}

		}

		$amount = round($amount*($obBServ->simple_percent/100),6);

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
	public function countingComplexBonus($model,$paymentBase = BonusScheme::BASE_SALE)
	{
		$arCuserGroup = PaymentsManager::getUserByGroup($model->cuser_id);  //получаем пользователей группы
		$inActivePeriod = (int)Yii::$app->config->get('c_inactivity',0);  //период бездействия в месяцах
		

		$saleUser = NULL;   //определеяем кому начислять бонус
		$obSale = PaymentsSale::find()
			->where(['cuser_id' => $arCuserGroup])
			->orderBy(['sale_date' => SORT_ASC,'id' => SORT_ASC])
			->one();

		if($paymentBase == BonusScheme::BASE_PAYMENT && empty($obSale))	//если не было продажи и тип базы начисления за каждый платеж, то вернем false
			return FALSE;

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
			if(empty($obLast)) {
				if($paymentBase == BonusScheme::BASE_PAYMENT)
					return FALSE;

				$saleUser = $model->saleUser;
			}
			else
				$saleUser = $obSale->buser_id;
		}

		if(!is_null($this->onlyForId) && $this->onlyForId != $saleUser)
			return false;

		$arExcept = BonusSchemeExceptCuser::getExceptSchemesForCuser($arCuserGroup);	//сземы искллючения для пользователя
		if($paymentBase == BonusScheme::BASE_SALE)
			$paymentBase = [BonusScheme::BASE_SALE,BonusScheme::BASE_PAYMENT];

		$obScheme = BonusScheme::find()  //ищем схему для компании
			->joinWith('cuserID')
			->joinWith('usersID')
			->where([
				BonusScheme::tableName().'.type' => BonusScheme::TYPE_COMPLEX_TYPE,
				BonusSchemeToBuser::tableName().'.buser_id' => $saleUser,
				BonusSchemeToCuser::tableName().'.cuser_id' => $model->cuser_id,
				'payment_base' => $paymentBase
			]);
			if(!empty($arExcept))
				$obScheme->andWhere(['NOT IN',BonusScheme::tableName().'.id',$arExcept]);
			$obScheme = $obScheme->orderBy(['payment_base' => SORT_DESC,BonusScheme::tableName().'.updated_at' => SORT_DESC])->one();

		if(!$obScheme)  //если нет схемы для компании, ищем общую
		{
			$obScheme = BonusScheme::find()//получаем схему бонуса для пользователя.
			->joinWith('cuserID')
				->joinWith('usersID')
				->where([
					BonusScheme::tableName() . '.type' => BonusScheme::TYPE_COMPLEX_TYPE,
					'payment_base' => $paymentBase
				])
				->andWhere([BonusSchemeToBuser::tableName() . '.buser_id' => $saleUser]);
			if (!empty($arExcept))
				$obScheme->andWhere(['NOT IN', BonusScheme::tableName() . '.id', $arExcept]);
			$obScheme = $obScheme->andWhere(BonusSchemeToCuser::tableName() . '.scheme_id IS NULL')
				->orderBy(['payment_base' => SORT_DESC,BonusScheme::tableName() . '.updated_at' => SORT_DESC])
				->one();
		}

		if(empty($obScheme))
			return FALSE;

		//костыли
		if(in_array($model->cuser_id,[6517,8753,208]) && $obScheme->id == 3 && $model->pay_date < strtotime('01.02.2016'))
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


		if(is_array($obBServ->legal_person) &&  //проверяем не указано ли для Юр. лица отнимать НАЛОГ от платежа
			isset($obBServ->legal_person[$model->legal_id]) &&
			isset($obBServ->legal_person[$model->legal_id]['deduct']) &&
			isset($obBServ->legal_person[$model->legal_id]['deduct']) == 1)
		{
			$obCuser = CUser::find()->select(['id','is_resident'])->where(['id' => $model->cuser_id])->one();	//пользователь
			if(!$obCuser)
				throw  new NotFoundHttpException();

			$key = $obCuser->is_resident ? 'res' : 'not_res';
			if(isset($obBServ->legal_person[$model->legal_id][$key]))
			{
				$tax = NULL;
				if(isset($obBServ->legal_person[$model->legal_id][$key.'_tax']) && is_numeric($obBServ->legal_person[$model->legal_id][$key.'_tax']))
					$tax = $obBServ->legal_person[$model->legal_id][$key.'_tax'];

				$amount = CustomHelper::getVatMountByAmount($amount,$tax); //отнимем от суммы платежа налог
			}

		}


		$amount = $amount*($percent/100);

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
	protected function addBonus($iUserID,$iPaymentID,$iSchemeID,$iServiceID,$iCuserID,$amount)
	{
		$bExistBonus = BUserBonus::find()
			->joinWith('scheme')
			->where([
				BUserBonus::tableName().'.buser_id' => $iUserID,
				BUserBonus::tableName().'.payment_id' => $iPaymentID,
				BUserBonus::tableName().'.service_id' => $iServiceID
			])
			->andWhere(['!=',BonusScheme::tableName().'.type',BonusScheme::TYPE_UNITS])		//исключаем юниты из проверки
			->exists();
		
		if($bExistBonus)
			return true;

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
	public function afterUpdate()
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
		$this->countingPartnerBonus($model);

		if($model->isSale && !empty($model->saleUser))  //если платеж продажа
		{
			$this->saveSale($model);
			$this->countingSimpleBonus($model);
			$this->countingComplexBonus($model);
		}else{
			$this->countingSimpleBonus($model,BonusScheme::BASE_PAYMENT);
			$this->countingComplexBonus($model,BonusScheme::BASE_SALE);
		}
	}

	/**
	 * @param Payments $model
	 * @return bool
	 * @throws NotFoundHttpException
	 * @throws ServerErrorHttpException
	 */
	public function countingPartnerBonus(Payments $model)
	{
		$arPartnersLead = PartnerCuserServ::find()		//get link partner - lead
			->where(['cuser_id' => $model->cuser_id,'service_id' => $model->service_id])
			->andWhere('archive IS NULL OR archive = 0')
			->all();

		if(empty($arPartnersLead))						//If links not found return FALSE
			return FALSE;

		$arPartnerIds = array_unique(ArrayHelper::getColumn($arPartnersLead,'partner_id'));

		$arPartner = CUser::find()->select(['id','partner_manager_id'])->where(['id' => $arPartnerIds])->partner()->all();	//Get partner with managers
		if(empty($arPartner))
			return FALSE;

		$arPartner = ArrayHelper::map($arPartner,'id','partner_manager_id');

		foreach ($arPartnersLead as $lead)
		{
			if(!isset($arPartner[$lead->partner_id]))
				continue;
			$iManID = $arPartner[$lead->partner_id]; 	//Partner manager
			/** @var BonusScheme $obScheme */
			$obScheme = $this->getBonusSchemeForPartnerBonus($lead->cuser_id,$iManID);
			if(empty($obScheme))
				continue;

			$obBServ = BonusSchemeServiceHistory::getCurrentBonusService($model->pay_date,$model->service_id,$obScheme->id);    //получаем параметры схемы
			if(empty($obBServ))
				continue;

			$arCuserGroups = [$lead->cuser_id];
			if($obScheme->grouping_type == BonusScheme::GROUP_BY_CMP_GROUP)
				$arCuserGroups = PaymentsManager::getUserByGroup($model->cuser_id);

			$iMonNum = $this->getMonthFromFirstPayment($arCuserGroups,$model->pay_date);	//Month number from first payment by Cuser
			if(empty($iMonNum))
				continue;

			$percent = $this->getPercentByMonthNumber($obBServ,$iMonNum);				//Percent for manager by month number

			if(empty($percent))
				continue;

			$amount = $this->getAmount($model);		//Get amount for counting bonus
			$amount = $this->getAmountWithoutTaxForLegalPerson($obBServ,$model->legal_id,$model->cuser_id,$amount);		//Get amount without tax if need

			$amount = $amount*($percent/100);		//Counting percent

			return $this->addBonus($iManID,$model->id,$obScheme->id,$model->service_id,$model->cuser_id,$amount);  //Add bonus for partner manager
		}

		return TRUE;
	}

	/**
	 * @param $iCuserID
	 * @param $iManID
	 * @return mixed
	 */
	protected function getBonusSchemeForPartnerBonus($iCuserID,$iManID)
	{
		$arExcept = BonusSchemeExceptCuser::getExceptSchemesForCuser([$iCuserID]);	//схемы искллючения для пользователя
		$obScheme = BonusScheme::find()  //получаем схему бонуса для пользователя с заднной компанией.
		->joinWith('cuserID')
			->joinWith('usersID')
			->where([
				BonusScheme::tableName().'.type' => BonusScheme::TYPE_COMPLEX_PARTNER,
				BonusSchemeToBuser::tableName().'.buser_id' => $iManID,
				BonusSchemeToCuser::tableName().'.cuser_id' => $iCuserID,
			]);
		if(!empty($arExcept))
			$obScheme->andWhere(['NOT IN',BonusScheme::tableName().'.id',$arExcept]);
		$obScheme = $obScheme->orderBy([BonusScheme::tableName().'.updated_at' => SORT_DESC])->one();

		if(!$obScheme) {
			$obScheme = BonusScheme::find()//получаем схему бонуса для пользователя.
				->joinWith('cuserID')
				->joinWith('usersID')
				->where([
					BonusScheme::tableName() . '.type' => BonusScheme::TYPE_COMPLEX_PARTNER,
					BonusSchemeToBuser::tableName() . '.buser_id' => $iManID,
				])
				->andWhere(BonusSchemeToCuser::tableName() . '.scheme_id IS NULL');
			if (!empty($arExcept))
				$obScheme->andWhere(['NOT IN', BonusScheme::tableName().'.id', $arExcept]);
			$obScheme = $obScheme->orderBy([BonusScheme::tableName() . '.updated_at' => SORT_DESC])->one();
		}

		return $obScheme;
	}

	/**
	 * @param array $arCuser
	 * @param $iPayDate
	 * @return int
	 */
	protected function getMonthFromFirstPayment(array $arCuser,$iPayDate)
	{
		$iPayDate = is_numeric($iPayDate) ? $iPayDate : strtotime($iPayDate);
		$obPayment = Payments::find()
			->select(['id','pay_date'])
			->where(['cuser_id' => $arCuser])
			->andWhere('pay_date < :iPayDate')
			->params([':iPayDate' => $iPayDate])
			->orderBy(['pay_date' => SORT_ASC])
			->one();

		if(!$obPayment)
			return 1;

		$date1 = new \DateTime();
		$date1->setTimestamp($iPayDate);
		$date2 = new \DateTime();
		$date2->setTimestamp($obPayment->pay_date);
		$interval = $date1->diff($date2);
		unset($date1,$date2);
		return ((int)$interval->m)+1;    //вренем разницу в месяцах между двумя датами

	}

	/**
	 * @param $obBServ
	 * @param $iMonNum
	 * @return mixed|null
	 */
	protected function getPercentByMonthNumber($obBServ,$iMonNum)
	{
		$percent = NULL;
		if(is_array($obBServ->month_percent) && isset($obBServ->month_percent[$iMonNum]) && !empty($obBServ->month_percent[$iMonNum]))
			$percent = $obBServ->month_percent[$iMonNum];
		return $percent;
	}

	/**
	 * @param $obBServ
	 * @param $iLegalID
	 * @param $iCuserID
	 * @param $amount
	 * @return float
	 * @throws NotFoundHttpException
	 */
	protected function getAmountWithoutTaxForLegalPerson($obBServ,$iLegalID,$iCuserID,$amount)
	{
		if(is_array($obBServ->legal_person) &&
			isset($obBServ->legal_person[$iLegalID],$obBServ->legal_person[$iLegalID]['deduct']) &&
			$obBServ->legal_person[$iLegalID]['deduct'] == 1)
		{
			$obCuser = CUser::find()->select(['id','is_resident'])->where(['id' => $iCuserID])->one();	//пользователь
			if(!$obCuser)
				throw  new NotFoundHttpException();

			$key = $obCuser->is_resident ? 'res' : 'not_res';
			if(isset($obBServ->legal_person[$iLegalID][$key]))
			{
				$tax = NULL;
				if(isset($obBServ->legal_person[$iLegalID][$key.'_tax']) && is_numeric($obBServ->legal_person[$iLegalID][$key.'_tax']))
					$tax = $obBServ->legal_person[$iLegalID][$key.'_tax'];

				$amount = CustomHelper::getVatMountByAmount($amount,$tax); //отнимем от суммы платежа налог
			}
		}
		return $amount;
	}
}