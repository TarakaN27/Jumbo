<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 18.1.16
 * Time: 16.06
 */

namespace common\components\behavior\Task;


use common\models\CrmTask;
use yii\base\Behavior;

class TaskActionBehavior extends Behavior
{

	/**
	 * Назначаем событиям обработчики
	 * @return array
	 */
	public function events()
	{
		return [
			CrmTask::EVENT_UPDATE_DIALOG => 'updateDialog'
		];
	}

	/**
	 *
	 */
	public function updateDialog()
	{
		$this->owner->updateUserForDialog();
	}

}