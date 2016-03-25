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
use common\models\BUserBonus;
use common\models\CUserGroups;
use common\models\CuserToGroup;
use common\models\Services;
use yii\base\Behavior;
use common\models\Payments;
use common\models\AbstractActiveRecord;
use backend\models\BUser;
use common\models\CUser;
use yii\db\Query;
use yii\web\ServerErrorHttpException;

class PaymentBonusBehavior extends Behavior
{

	public function events()
	{
		return [
			Payments::EVENT_BEFORE_INSERT => 'beforeInsert',
			Payments::EVENT_AFTER_INSERT => 'afterInsert',
			Payments::EVENT_AFTER_DELETE => 'afterDelete',
		];
	}


	public function afterInsert()
	{
		/** @var Payments $model */
		$model = $this->owner;

		$iPayID = $model->id;             // ID платежа
		$iCUserID = $model->cuser_id;     // ID контрагента
		$sDate = $model->pay_date;        // Дата платежа
		$iService = $model->service_id;   // ID услуги

		$this->countingUnits($model,$iPayID,$iCUserID,$sDate,$iService);












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
			return TRUE;

		$obSchemeService = BonusSchemeServiceHistory::getCurrentBonusService($model->pay_date,$iService,$obManager->id,BonusScheme::TYPE_UNITS);
		/** @var BonusScheme $obScheme */
		if(!$obSchemeService || !is_object( $obScheme = $obSchemeService->scheme))
			return TRUE;

		if($obSchemeService->unit_multiple) //если зачисляется юнит по каждому платежу
		{
			$this->addBonus($iCUserID,$iPayID,$obSchemeService->scheme_id,$iService,$iCUserID,$obSchemeService->cost);
		}else{
			//необходимо проверить были ли бонусы по данной услуге уже зачисленны
			$beginMonth = CustomHelper::getBeginMonthTime($model->pay_date);    //время начала месяца, когда ьыл совершен платеж
			$endMonth = CustomHelper::getEndMonthTime($model->pay_date);    //окончание месяца

			$obBonus = BUserBonus::find()
				->alias('bb')
				->joinWith('payment p');

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

			$obBonus->where(['cuser_id' => $arCuser])
				->andWhere('p.pay_date >= :beginDate AND p.pay_date <=:endDate')
				->andWhere(['service_id' => $iService])
				->params([':beginDate' => $beginMonth,':endDate' => $endMonth]);

			if($obBonus->exists())
				return TRUE;

			$this->addBonus($iCUserID,$iPayID,$obSchemeService->scheme_id,$iService,$iCUserID,$obSchemeService->cost);
		}

		return TRUE;
	}



	protected function countingSimpleBonus()
	{
		//if()





	}

	/**
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
		return TRUE;
	}
}