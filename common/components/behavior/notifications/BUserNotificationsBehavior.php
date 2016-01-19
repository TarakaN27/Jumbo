<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 14.1.16
 * Time: 11.02
 */

namespace common\components\behavior\notifications;


use common\components\notification\RedisNotification;
use yii\base\Behavior;
use yii\db\ActiveRecord;

class BUserNotificationsBehavior extends Behavior
{
	/**
	 * Назначаем событиям обработчики
	 * @return array
	 */
	public function events()
	{
		return [
			ActiveRecord::EVENT_AFTER_DELETE => 'afterDelete',
		];
	}

	/**
	 *
	 */
	public function afterDelete()
	{
		RedisNotification::removeListNewTaskForUsers([$this->owner->id]);
		RedisNotification::removeDialogListForUsers([$this->owner->id]);
		return TRUE;
	}

}