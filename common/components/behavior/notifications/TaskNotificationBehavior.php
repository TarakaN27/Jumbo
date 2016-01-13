<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 13.1.16
 * Time: 16.55
 */

namespace common\components\behavior\notifications;


use common\components\notification\TabledNotification;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\helpers\Html;

class TaskNotificationBehavior extends Behavior
{
	/**
	 * Назначаем событиям обработчики
	 * @return array
	 */
	public function events()
	{
		return [
			ActiveRecord::EVENT_AFTER_INSERT => 'afterInsert',
		];
	}

	public function afterInsert()
	{
		$owner = $this->owner;

		$arUsers = [$owner->assigned_id];

		$arAcc = $owner->crmTaskAccomplices;
		if(is_array($arAcc))
			foreach($arAcc as $acc)
				$arUsers[] = $acc->buser_id;

		return TabledNotification::addMessage(
			\Yii::t('app/crm','You have new task'),
			Html::a($owner->title,['/crm/task/view','id' => $owner->id]),
			TabledNotification::TYPE_PRIVATE,
			TabledNotification::NOTIF_TYPE_SUCCESS,
			$arUsers
		);
	}

}