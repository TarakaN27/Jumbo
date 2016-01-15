<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 15.1.16
 * Time: 10.04
 */

namespace common\components\behavior\notifications;


use common\models\AbstractActiveRecord;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use common\components\notification\RedisNotification;

class DialogNotificationBehavior extends Behavior
{
	/**
	 * Назначаем событиям обработчики
	 * @return array
	 */
	public function events()
	{
		return [
			ActiveRecord::EVENT_AFTER_INSERT => 'afterInsert',
			AbstractActiveRecord::EVENT_VIEWED => 'viewed'
		];
	}

	/**
	 *
	 */
	public function afterInsert()
	{
		//$arUsers = $this->owner
		RedisNotification::addNewDialogToListForUsers(,$this->owner->id);
		//RedisNotification::removeListNewTaskForUsers([$this->owner->id]);
		return TRUE;
	}

	/**
	 *
	 */
	public function viwed()
	{

	}
}