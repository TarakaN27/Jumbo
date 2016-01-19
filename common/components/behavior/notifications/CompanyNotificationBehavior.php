<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 19.1.16
 * Time: 9.53
 */

namespace common\components\behavior\notifications;


use common\components\notification\RedisNotification;
use common\components\notification\TabledNotification;
use common\models\AbstractActiveRecord;
use yii\base\Behavior;
use common\models\CUser;

class CompanyNotificationBehavior extends Behavior
{

	protected
		$arUsers = [],
		$oldManager = NULL,
		$arChangedFields = [],
		$arCheckField = [
			'archive' => 'getArchiveStr',
			'manager_id' => 'manager'
		];

	/**
	 * Назначаем событиям обработчики
	 * @return array
	 */
	public function events()
	{
		return [
			AbstractActiveRecord::EVENT_SAVE_DONE => 'afterInsert',
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
		/** @var CUSer $model */
		$model = $this->owner;
		if($model->manager_id != $model->created_by)
		{
			RedisNotification::addNewCompanyToListForUsers([$model->manager_id],$model->id);
			TabledNotification::addMessage(\Yii::t('app/crm','New company {user}',[
				'user' => \Yii::$app->user->identity->getFio()
			]),
				$model->getInfo(),
				TabledNotification::TYPE_PRIVATE,
				TabledNotification::NOTIF_TYPE_INFO,
				[(int)$model->manager_id]);
		}
		return TRUE;
	}

	public function afterUpdate()
	{
		/** @var CUSer $model */
		$model = $this->owner;
		if(
			$model->manager_id != \Yii::$app->user->id &&
			!empty($this->oldManager)
			&& $this->oldManager != $model->manager_id
			&& $model->created_by != $model->manager_id
		)
		{
			RedisNotification::addNewCompanyToListForUsers([(int)$model->manager_id],$model->id);
			TabledNotification::addMessage(\Yii::t('app/crm','New company {user}',[
				'user' => \Yii::$app->user->identity->getFio()
			]),
				$model->getInfo(),
				TabledNotification::TYPE_PRIVATE,
				TabledNotification::NOTIF_TYPE_INFO,
				[(int)$model->manager_id]);

			RedisNotification::removeCompanyFromListForUser($this->oldManager,$model->id);
		}

	}

	/**
	 * @return bool
	 */
	public function beforeUpdate()
	{
		$oldValue = $this->owner->getOldAttributes();
		$model = $this->owner;
		if($model->isAttributeChanged('manager_id') && $oldValue['manager_id'] != $model->manager_id)
		{
			$this->oldManager = (int) $oldValue['manager_id'];
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
		RedisNotification::removeCompanyFromListForUsers([$model->manager_id,$model->created_by],$model->id);
	}

	public function viewed()
	{
		$model = $this->owner;
		RedisNotification::removeCompanyFromListForUser(\Yii::$app->user->id,$model->id);
	}
}