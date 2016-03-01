<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 16.1.16
 * Time: 14.55
 */

namespace common\components\behavior\notifications;

use common\components\helpers\CustomHelper;
use common\models\CrmTaskAccomplices;
use common\models\Dialogs;
use yii\base\Behavior;
use common\models\AbstractActiveRecord;
use common\components\notification\RedisNotification;
use yii\db\Query;
use yii\helpers\Html;
use common\components\notification\TabledNotification;
use Yii;

class DialogMessagesBehavior extends Behavior
{
	/**
	 * Назначаем событиям обработчики
	 * @return array
	 */
	public function events()
	{
		return [
			AbstractActiveRecord::EVENT_AFTER_INSERT => 'afterInsert',
			AbstractActiveRecord::EVENT_SAVE_DONE => 'saveDone',
			AbstractActiveRecord::EVENT_VIEWED => 'viewed'
		];
	}

	/**
	 * @return bool
	 */
	public function afterInsert()
	{
		/** @var Dialogs $obDialog */
		$obDialog = Dialogs::findOne($this->owner->dialog_id);
		if(!$obDialog)
			return FALSE;

		$obDialog->updateUpdatedAt();  //перемещаем диалог вверх

		$arUsers = $obDialog->getUsersIdsForDialog();   //получаем всех пользователй диалога

		$arUsers = CustomHelper::removeArrayItemByValue($arUsers,\Yii::$app->user->id); //удаляем из пользователй инициализатора действия

		if(!empty($arUsers))
		{
			RedisNotification::addNewDialogToListForUsers($arUsers,$obDialog->id);   //добавляем балун

			$title = strip_tags(CustomHelper::cuttingString($this->owner->msg));
			if(!empty($obDialog->crm_task_id))
			{
				if(empty($title))
					$title = Yii::t('app/crm','Go to task');
				$title = Html::a($title,['/crm/task/view','id' => $obDialog->crm_task_id]);
			}


			if(!empty($obDialog->crm_cmp_contact_id))
			{
				if(empty($title))
					$title = Yii::t('app/crm','Go to contact');
				$title = Html::a($title,['/crm/contact/view','id' => $obDialog->crm_cmp_contact_id]);
			}


			if(!empty($obDialog->crm_cmp_id))
			{
				if(empty($title))
					$title = Yii::t('app/crm','Go to company');
				$title = Html::a($title,['/crm/company/view','id' => $obDialog->crm_cmp_id]);
			}


			$obUser = Yii::$app->user->identity->getFio();

			TabledNotification::addMessage(
				Yii::t('app/crm','New message from {user}',[
					'user' => $obUser
				]),
				$title,
				TabledNotification::TYPE_PRIVATE,
				TabledNotification::NOTIF_TYPE_WARNING,
				array_values($arUsers)
			);
		}

	}

	public function saveDone()
	{

	}

}