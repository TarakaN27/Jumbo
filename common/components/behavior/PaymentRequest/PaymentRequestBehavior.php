<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 20.1.16
 * Time: 12.53
 * Поведение
 * Создаем задачу и оповещения, когда создается запрос на платеж
 */

namespace common\components\behavior\PaymentRequest;

use backend\models\BUser;
use common\models\AbstractActiveRecord;
use common\models\CrmTask;
use common\models\PaymentRequest;
use yii\base\Behavior;
use Yii;

class PaymentRequestBehavior extends Behavior
{


	/**
	 * Назначаем событиям обработчики
	 * @return array
	 */
	public function events()
	{
		return [
			AbstractActiveRecord::EVENT_AFTER_INSERT => 'afterInsert',
			PaymentRequest::EVENT_PIN_MANAGER => 'pinManager'
		];
	}

	/**
	 *
	 */
	public function afterInsert()
	{
		/** @var PaymentRequest $model */
		$model = $this->owner;

		/** @var CrmTask $obTask */
		$obTask = new CrmTask();
		$obTask->payment_request = $model->id;
		$obTask->type = CrmTask::TYPE_TASK;
		$obTask->priority = CrmTask::PRIORITY_HIGH;

		$obTask->assigned_id = empty($model->manager_id) ? Yii::$app->user->id : $model->manager_id;
		$obTask->created_by = Yii::$app->user->id;

		$theme = Yii::t('app/crm','New payment request ');

		if($model->is_unknown != $model::YES && !empty($model->cntr_id))
		{
			$obCnt = $model->cuser;
			if(is_object($obCnt))
			{
				$theme.= ' "'.$obCnt->getInfo().'"';
			}
		}else{
			$theme.= ' "'.$model->user_name.'"';
		}

		$obTask->title = $theme;
		if(empty($model->manager_id))   //если контрагент не известен, то вешаем всех менеджеров в соисполнители
		{
			$arManagers = BUser::getManagersArr();
			if(!empty($arManagers))
				foreach($arManagers as $man)
					$obTask->arrAcc [] = $man->id;
		}

		$obTask->createTask(Yii::$app->user->id);
	}

	/**
	 * После назначения менеджера нужно покорректировать задачу и диалог задачи.
	 * @return bool
	 */
	public function pinManager()
	{
		/** @var PaymentRequest $model */
		$model = $this->owner;

		//$arUser = [$model->manager_id,$model->owner_id];

		/** @var CrmTask $obTask */
		$obTask = $model->task;

		if(is_object($obTask))
		{
			$obTask->assigned_id = $model->manager_id;
			$obTask->save();
			$obTask->unlinkAll('busersAccomplices',TRUE);
			$obTask->callTriggerUpdateDialog();
		}

		return TRUE;
	}

}