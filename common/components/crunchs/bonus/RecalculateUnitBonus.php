<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 31.3.16
 * Time: 17.28
 */

namespace common\components\crunchs\bonus;

use common\models\BonusSchemeExceptCuser;
use common\models\BUserBonus;
use common\models\PaymentRequest;
use common\models\Payments;
use yii\web\ServerErrorHttpException;
use backend\models\BUser;
use common\models\BonusScheme;
use common\models\BonusSchemeToBuser;
use common\models\BonusSchemeToCuser;
use common\models\AbstractActiveRecord;
use common\models\BonusSchemeServiceHistory;
use common\components\helpers\CustomHelper;
use common\models\CuserToGroup;
use yii\db\Query;


class RecalculateUnitBonus
{
	public function run()
	{

		$arPayments = Payments::find()->orderBy(['pay_date' => SORT_DESC])->all();
		/** @var Payments $model */
		foreach($arPayments as $model)
		{
			/** @var BUser $obManager */
			$obManager = BUser::find()      //находим менеджера, для которого зачисляется unit
			->select(['b.id','b.allow_unit'])
				->alias('b')
				->leftJoin(PaymentRequest::tableName().' as r','r.manager_id = b.id')
				->where(['r.id' => $model->prequest_id])
				->one();

			if(!$obManager || $obManager->allow_unit != AbstractActiveRecord::YES)  //проверяем нашли ли менеджера и разрешено ли менеджеру накапливать Units
				continue;

			$obScheme = BonusScheme::find()  //получаем схему бонуса для пользователя.
			->joinWith('cuserID')
				->joinWith('usersID')
				->joinWith('exceptCusers')
				->where([BonusScheme::tableName().'.type' => BonusScheme::TYPE_UNITS])
				->andWhere([BonusSchemeToBuser::tableName().'.buser_id' => $obManager->id])
				->andWhere(BonusSchemeExceptCuser::tableName().'.cuser_id != :idCUser OR '.BonusSchemeExceptCuser::tableName().'.cuser_id is NULL')
				->orderBy(BonusSchemeToCuser::tableName().'.cuser_id IS NULL ASC , '.BonusScheme::tableName().'.updated_at DESC')
				->params([':idCUser' => $model->cuser_id])
				->one();

			if(empty($obScheme))
				continue;

			$obSchemeService = BonusSchemeServiceHistory::getCurrentBonusService($model->pay_date,$model->service_id,$obScheme->id); //находим параметры по схеме
			/** @var BonusScheme $obScheme */
			if(!$obSchemeService || !is_object( $obScheme = $obSchemeService->scheme))
				continue;

			if($obSchemeService->unit_multiple) //если зачисляется юнит по каждому платежу
			{
				$this->addBonus($obManager->id,$model->id,$obSchemeService->scheme_id,$model->service_id,$model->cuser_id,$obSchemeService->cost,$model);
			}else{
				//необходимо проверить были ли бонусы по данной услуге уже зачисленны
				$beginMonth = CustomHelper::getBeginMonthTime($model->pay_date);    //время начала месяца, когда был совершен платеж
				$endMonth = CustomHelper::getEndMonthTime($model->pay_date);    //окончание месяца

				$obBonus = BUserBonus::find()
					->alias('bb')
					->joinWith('payment p')
					->joinWith('scheme sc');

				$arCuser = [$model->cuser_id];

				if($obScheme->grouping_type == BonusScheme::GROUP_BY_CMP_GROUP)
				{
					$groupIdsTmp = CuserToGroup::find()->select(['group_id'])->where(['cuser_id' => $model->cuser_id])->all();
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
					->andWhere(['bb.service_id' => $model->service_id])
					->params([':beginDate' => $beginMonth,':endDate' => $endMonth]);

				if($obBonus->exists())
					continue;

				$this->addBonus($obManager->id,$model->id,$obSchemeService->scheme_id,$model->service_id,$model->cuser_id,$obSchemeService->cost,$model);

			}
		}
		echo 'done';
		return TRUE;
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
	protected function addBonus($iUserID,$iPaymentID,$iSchemeID,$iServiceID,$iCuserID,$amount,$model)
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

}