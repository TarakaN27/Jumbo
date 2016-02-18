<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 16.2.16
 * Time: 16.42
 */

namespace common\components\behavior\Task;


use common\models\CuserQuantityHour;
use yii\base\Behavior;
use common\models\CrmTask;
use yii\web\ServerErrorHttpException;

class TaskQuantityHoursBehavior extends Behavior
{

	protected
		$oldSpentTime = NULL;

	/**
	 * Назначаем событиям обработчики
	 * @return array
	 */
	public function events()
	{
		return [
			CrmTask::EVENT_AFTER_INSERT => 'afterInsert',
			CrmTask::EVENT_AFTER_DELETE => 'afterDelete',
			CrmTask::EVENT_AFTER_UPDATE => 'afterUpdate',
			CrmTask::EVENT_BEFORE_UPDATE => 'beforeUpdate'
		];
	}

	/**
	 * @return bool
	 * @throws ServerErrorHttpException
	 */
	public function afterInsert()
	{
		/** @var CrmTaskLogTime $model */
		$model = $this->owner;

		$obTask = CrmTask::find()->select(['cmp_id'])->where(['id' => $model->task_id])->one();
		if(empty($obTask))
			return FALSE;

		$obQHour = CuserQuantityHour::find()->where(['cuser_id' => $obTask->cmp_id])->one();
		if(!$obQHour)
		{
			$obQHour = new CuserQuantityHour();
			$obQHour->cuser_id = $obTask->cmp_id;
			$obQHour->hours = 0;
		}

		$hours = round($model->spend_time/3600,2);
		$obQHour->spent_time+=$hours;
		if(!$obQHour->save())
			throw new ServerErrorHttpException('Can not save quantity hour');

		return TRUE;
	}

	/**
	 *
	 */
	public function beforeUpdate()
	{
		$this->oldSpentTime = $this->owner->getOldAttribute('spend_time');
	}

	/**
	 * @return bool
	 * @throws ServerErrorHttpException
	 */
	public function afterUpdate()
	{
		/** @var CrmTaskLogTime $model */
		$model = $this->owner;
		if($model->spend_time == $this->oldSpentTime)
			return TRUE;

		$obTask = CrmTask::find()->select(['cmp_id'])->where(['id' => $model->task_id])->one();
		if(empty($obTask))
			return FALSE;

		$obQHour = CuserQuantityHour::find()->where(['cuser_id' => $obTask->cmp_id])->one();
		if(!$obQHour)
		{
			$obQHour = new CuserQuantityHour();
			$obQHour->cuser_id = $obTask->cmp_id;
			$obQHour->hours = 0;
		}

		$hours = round(($model->spend_time-$this->oldSpentTime)/3600,2);
		$obQHour->spent_time+=$hours;
		if(!$obQHour->save())
			throw new ServerErrorHttpException('Can not save quantity hour');

		return TRUE;

	}

	/**
	 * @return bool
	 * @throws ServerErrorHttpException
	 */
	public function afterDelete()
	{
		/** @var CrmTaskLogTime $model */
		$model = $this->owner;

		$obTask = CrmTask::find()->select(['cmp_id'])->where(['id' => $model->task_id])->one();
		if(empty($obTask))
			return FALSE;

		$obQHour = CuserQuantityHour::find()->where(['cuser_id' => $obTask->cmp_id])->one();
		if(!$obQHour)
			return TRUE;

		$hours = round($model->spend_time/3600,2);
		$obQHour->spent_time-=$hours;
		if(!$obQHour->save())
			throw new ServerErrorHttpException('Can not save quantity hour');

		return TRUE;
	}

}