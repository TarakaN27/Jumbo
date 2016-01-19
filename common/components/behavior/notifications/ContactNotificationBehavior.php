<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 19.1.16
 * Time: 12.39
 */

namespace common\components\behavior\notifications;

use yii\base\Behavior;
use common\components\notification\RedisNotification;
use common\components\notification\TabledNotification;
use common\models\AbstractActiveRecord;
use common\models\CUser;
use yii\helpers\Html;

class ContactNotificationBehavior extends Behavior
{

	protected
		$arUsers = [],
		$oldManager = NULL,
		$arChangedFields = [],
		$arCheckField = [
			'type' => 'getTypeStr',
			'assigned_at' => 'assigned_at'
		];

	/**
	 * Назначаем событиям обработчики
	 * @return array
	 */
	public function events()
	{
		return [
			AbstractActiveRecord::EVENT_AFTER_INSERT => 'afterInsert',
			AbstractActiveRecord::EVENT_AFTER_UPDATE => 'afterUpdate',
			AbstractActiveRecord::EVENT_BEFORE_UPDATE => 'beforeUpdate',
			AbstractActiveRecord::EVENT_BEFORE_DELETE => 'beforeDelete',
			AbstractActiveRecord::EVENT_AFTER_DELETE => 'afterDelete',
			AbstractActiveRecord::EVENT_VIEWED => 'viewed'
		];
	}

	/**
	 *
	 */
	public function afterInsert()
	{
		$model = $this->owner;
		if($model->assigned_at != $model->created_by)
		{
			RedisNotification::addNewContactToListForUsers([$model->assigned_at],$model->id);
			TabledNotification::addMessage(\Yii::t('app/crm','New contact ({user})',[
				'user' => \Yii::$app->user->identity->getFio()
			]),
				Html::a($model->fio,['/crm/contact/view','id' => $model->id]),
				TabledNotification::TYPE_PRIVATE,
				TabledNotification::NOTIF_TYPE_INFO,
				[(int)$model->assigned_at]);
		}
		return TRUE;
	}

	public function afterUpdate()
	{

		$model = $this->owner;
		if(
			$model->assigned_at != \Yii::$app->user->id &&
			!empty($this->oldManager)
			&& $this->oldManager != $model->assigned_at
			&& $model->created_by != $model->assigned_at
		)
		{
			RedisNotification::addNewContactToListForUsers([(int)$model->assigned_at],$model->id);
			TabledNotification::addMessage(\Yii::t('app/crm','New contact {user}',[
				'user' => \Yii::$app->user->identity->getFio()
			]),
				$model->fio,
				TabledNotification::TYPE_PRIVATE,
				TabledNotification::NOTIF_TYPE_INFO,
				[(int)$model->assigned_at]);

			RedisNotification::removeContactFromListForUser($this->oldManager,$model->id);
		}

	}

	/**
	 * @return bool
	 */
	public function beforeUpdate()
	{
		$oldValue = $this->owner->getOldAttributes();
		$model = $this->owner;
		if($model->isAttributeChanged('assigned_at') && $oldValue['assigned_at'] != $model->assigned_at)
		{
			$this->oldManager = (int) $oldValue['assigned_at'];
		}

		return TRUE;
	}

	public function beforeDelete()
	{

	}

	/**
	 *
	 */
	public function afterDelete()
	{
		$model = $this->owner;
		RedisNotification::removeContactFromListForUsers([$model->assigned_at,$model->created_by],$model->id);
	}

	/**
	 *
	 */
	public function viewed()
	{
		$model = $this->owner;
		RedisNotification::removeContactFromListForUser(\Yii::$app->user->id,$model->id);
	}
}