<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 15.1.16
 * Time: 10.04
 */

namespace common\components\behavior\notifications;


use common\components\helpers\CustomHelper;
use common\components\notification\TabledNotification;
use common\models\AbstractActiveRecord;
use yii\base\Behavior;
use common\components\notification\RedisNotification;
use Yii;
use yii\helpers\Html;

class DialogNotificationBehavior extends Behavior
{
	/**
	 * Назначаем событиям обработчики
	 * @return array
	 */
	public function events()
	{
		return [
			AbstractActiveRecord::EVENT_SAVE_DONE => 'afterInsert',
			AbstractActiveRecord::EVENT_VIEWED => 'viewed'
		];
	}

	/**
	 * @return bool
	 */
	public function afterInsert()
	{
		$arTmpUsers = $this->owner->busersIds;  //получаем всех пользователй диалога
		if(empty($arTmpUsers))
			return TRUE;
		$arUsers = [];
		foreach($arTmpUsers as $obU)
		{
			if($this->owner->buser_id != $obU->buser_id)    //исключим из оповещений овтора диалога
				$arUsers[] = $obU->buser_id;
		}

		if(!empty($arUsers))
		{
			RedisNotification::addNewDialogToListForUsers($arUsers,$this->owner->id);   //добавляем балун

			$title = $this->owner->theme;
			if(!empty($this->owner->crm_task_id))
				$title = Html::a($title,['/crm/task/view','id' => $this->owner->crm_task_id]);

			if(!empty($this->owner->crm_cmp_contact_id))
				$title = Html::a($title,['/crm/contact/view','id' => $this->owner->crm_cmp_contact_id]);

			if(!empty($this->owner->crm_cmp_id))
				$title = Html::a($title,['/crm/company/view','id' => $this->owner->crm_cmp_id]);

			$obUser = $this->owner->owner;

			TabledNotification::addMessage(
				Yii::t('app/crm','New dialog from {user}',[
					'user' => is_object($obUser) ? $obUser->getFio() : 'N/A'
				]),
				CustomHelper::cuttingString($title),
				TabledNotification::TYPE_PRIVATE,
				TabledNotification::NOTIF_TYPE_WARNING,
				array_values($arUsers)
			);

		}
		return TRUE;
	}


	/**
	 *
	 */
	public function viewed()
	{
		RedisNotification::removeDialogFromListForUser(\Yii::$app->user->id,$this->owner->id);
	}
}