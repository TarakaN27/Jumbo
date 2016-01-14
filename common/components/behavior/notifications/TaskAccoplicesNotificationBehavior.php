<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 14.1.16
 * Time: 10.55
 */

namespace common\components\behavior\notifications;


use common\components\notification\RedisNotification;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use common\components\notification\TabledNotification;
use yii\helpers\Html;

class TaskAccoplicesNotificationBehavior extends Behavior
{
	/**
	 * Назначаем событиям обработчики
	 * @return array
	 */
	public function events()
	{
		return [
			ActiveRecord::EVENT_AFTER_INSERT => 'afterInsert',
			ActiveRecord::EVENT_AFTER_DELETE => 'afterDelete',
		];
	}

	/**
	 * @return mixed
	 */
	public function afterInsert()
	{
		$task = $this->owner->task;

		RedisNotification::addNewTaskToList([$this->owner->buser_id],$this->owner->task_id);

		return // добавляем realtime уведомление(nodejs, socket.io,redis)
			TabledNotification::addMessage(
				\Yii::t('app/crm','You have new task'),
				Html::a($task->title,['/crm/task/view','id' => $this->owner->task_id]),
				TabledNotification::TYPE_PRIVATE,
				TabledNotification::NOTIF_TYPE_SUCCESS,
				[$this->owner->buser_id]
			);
	}

	public function afterDelete()
	{
		RedisNotification::removeNewTaskFromList([$this->owner->buser_id],$this->owner->task_id);
	}
}