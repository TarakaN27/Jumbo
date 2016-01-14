<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 13.1.16
 * Time: 16.55
 */

namespace common\components\behavior\notifications;


use common\components\notification\RedisNotification;
use common\components\notification\TabledNotification;
use common\models\AbstractActiveRecord;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\helpers\Html;

class TaskNotificationBehavior extends Behavior
{
	protected
		$oldAssigned,
		$oldAccomplices = [],
		$arTmpUsers = [];

	/**
	 * Назначаем событиям обработчики
	 * @return array
	 */
	public function events()
	{
		return [
			ActiveRecord::EVENT_AFTER_INSERT => 'afterInsert',
			ActiveRecord::EVENT_BEFORE_DELETE => 'beforeDelete',
			ActiveRecord::EVENT_AFTER_DELETE => 'afterDelete',
			ActiveRecord::EVENT_BEFORE_UPDATE => 'beforeUpdate',
			ActiveRecord::EVENT_AFTER_UPDATE => 'afterUpdate',
			AbstractActiveRecord::EVENT_LINK => 'afterLink'
		];
	}

	/**
	 * @return bool
	 */
	public function beforeUpdate()
	{
		$this->oldAssigned = $this->owner->getOldAttribute('assigned_id'); //сохраняем старого отвественного
		$tmp = $this->owner->crmTaskAccomplices;
		if(!empty($tmp))
			foreach($tmp as $t)
				$this->oldAccomplices [] = $t->buser_id;
		return TRUE;
	}

	/**
	 * @return bool
	 */
	public function afterUpdate()
	{
		if($this->owner->assigned_id != $this->oldAssigned) //если изменился ответсвенный
		{
			RedisNotification::removeViewedNewTask($this->oldAssigned,$this->owner->id);    //удалим из списка новых тасков старого ответсвенного
			RedisNotification::addNewTaskToList([$this->owner->assigned_id],$this->owner->id); //добавим новому
			$this->addTabledNotification([$this->owner->assigned_id]);  //добавим реалтайм уведомление
		}

		return TRUE;
	}

	/**
	 * после связи
	 * @return bool
	 */
	public function afterLink()
	{
		$tmp = $this->owner->crmTaskAccomplices;    //получаем соисполнителей текущих

		$arNew = [];    //собираем ID соисполнителей
		foreach($tmp as $t)
			$arNew [] = $t->buser_id;

		if(empty($arNew) && !empty($this->oldAccomplices)) //если нет новых, но есть старые , удалиим старых
			RedisNotification::removeNewTaskFromList($this->oldAccomplices,$this->owner->id);

		if(empty($this->oldAccomplices) && !empty($arNew))  //если есть новые, но нет старых, добавим новых
			RedisNotification::addNewTaskToList($arNew,$this->owner->id);

		if(!empty($this->oldAccomplices) && !empty($arNew)) //если есть старые и новые
		{
			foreach($this->oldAccomplices as $keyOld => $old)   //собрем что нужно удалить а что добавить
				foreach($arNew as $keyNew => $new)
				{
					if($new == $old)
					{
						unset($this->oldAccomplices[$keyOld]);
						unset($arNew[$keyNew]);
					}
				}

			if(!empty($this->oldAccomplices))
				RedisNotification::removeNewTaskFromList($this->oldAccomplices,$this->owner->id);

			if(!empty($arNew))
				RedisNotification::addNewTaskToList($arNew,$this->owner->id);
		}

		return TRUE;
	}


	/**
	 * Получаем пользователей для уведомлений
	 * @return array
	 */
	protected function getAllUsers()
	{
		$owner = $this->owner;
		// соберем всех пользователй причастных к задаче
		$arUsers = [$owner->assigned_id];
		$arAcc = $owner->crmTaskAccomplices;
		if(is_array($arAcc))
			foreach($arAcc as $acc)
				$arUsers[] = $acc->buser_id;
		// исключим постановщика задачи. Он и так в курсе того, что задача поставлена
		foreach($arUsers as $key=>$user)
			if($user == $owner->created_by)
				unset($arUsers[$key]);

		return $arUsers;
	}

	/**
	 * Добавление realtime уведомления
	 * @param $arUsers
	 * @return mixed
	 */
	protected function addTabledNotification($arUsers)
	{
		return // добавляем realtime уведомление(nodejs, socket.io,redis)
			TabledNotification::addMessage(
				\Yii::t('app/crm','You have new task'),
				Html::a($this->owner->title,['/crm/task/view','id' => $this->owner->id]),
				TabledNotification::TYPE_PRIVATE,
				TabledNotification::NOTIF_TYPE_SUCCESS,
				$arUsers
			);
	}

	/**
	 * @return bool
	 */
	public function afterInsert()
	{
		$owner = $this->owner;
		// соберем всех пользователй причастных к задаче
		$arUsers = [$owner->assigned_id];
		$arAcc = $owner->arrAcc;
		if(is_array($arAcc))
			foreach($arAcc as $acc)
				$arUsers[] = $acc;
		// исключим постановщика задачи. Он и так в курсе того, что задача поставлена
		foreach($arUsers as $key=>$user)
			if($user == $owner->created_by)
				unset($arUsers[$key]);

		// если нет пользователей, для оповещения, то вернем TRUE
		if(empty($arUsers))
			return TRUE;

		//Добавляем realtime уведомление
		$this->addTabledNotification($arUsers);

		// добавляем в список новых задач redis запись о новой задаче
		RedisNotification::addNewTaskToList($arUsers,$owner->id);

		return TRUE;
	}

	/**
	 * После удаления задачи, удалим из списков новых задач
	 * @return bool
	 */
	public function afterDelete()
	{
		if(empty($this->arTmpUsers))
			return TRUE;

		RedisNotification::removeNewTaskFromList($this->arTmpUsers,$this->owner->id); //после удаления , удалим из списка новых
		return TRUE;
	}

	/**
	 *
	 * @return bool
	 */
	public function beforeDelete()
	{
		$this->arTmpUsers = $this->getAllUsers();   //перед удалением сохраним всех пользователей, которые причастны к задаче
		return TRUE;
	}

}