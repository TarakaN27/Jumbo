<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 14.1.16
 * Time: 9.59
 */

namespace common\components\notification;

use Yii;
class RedisNotification
{
	CONST
		MSG_KEY = 'umsg',
		FEED_KEY = 'ufeed',
		CONTACT_KEY = 'ucontact',
		COMPANY_KEY = 'ucompany',
		TASK_KEY = 'utask';

	/**
	 * Ключ для списка редис с новыми задачами
	 * @param $iUserID
	 * @return string
	 */
	public static function getNewTaskKey($iUserID)
	{
		return self::TASK_KEY.':id:'.$iUserID;
	}

	/**
	 * @param $iUserID
	 * @return string
	 */
	public static function getNewCompanyKey($iUserID)
	{
		return self::COMPANY_KEY.':id:'.$iUserID;
	}

	/**
	 * @param $iUserID
	 * @return string
	 */
	public static function getNewContactKey($iUserID)
	{
		return self::COMPANY_KEY.':id:'.$iUserID;
	}

	/**
	 * @param $iUserID
	 * @return string
	 */
	public static function getNewFeedKey($iUserID)
	{
		return self::FEED_KEY.':id:'.$iUserID;
	}

	/**
	 * @param $iUserID
	 * @return string
	 */
	public static function getNewMsgKey($iUserID)
	{
		return self::MSG_KEY.':id:'.$iUserID;
	}


	/**
	 * Удаляем просмотренную задачу из списка новых
	 * @param $iUserID
	 * @param $iTaskID
	 * @return bool
	 */
	public static function removeViewedNewTask($iUserID,$iTaskID)
	{
		$key = self::getNewTaskKey($iUserID);   //получаем ключ для новой задачи пользователя
		if(Yii::$app->redis->sismember($key,$iTaskID))
			return Yii::$app->redis->srem($key,$iTaskID); //удаляем из списка
		return TRUE;
	}

	/**
	 * Считаем сколько новых задач у нас в списке
	 * @param $iUserID
	 * @return int
	 */
	public static function getNewTaskCount($iUserID)
	{
		$key = RedisNotification::getNewTaskKey($iUserID);
		$count = \Yii::$app->redis->scard($key);
		return (int)$count;
	}

	/**
	 * Получаем список новых задач
	 * @param $iUserID
	 * @return array
	 */
	public static function getNewTaskList($iUserID)
	{
		$key = self::getNewTaskKey($iUserID);
		$tmp = Yii::$app->redis->smembers($key);
		return empty($tmp) ? [] : $tmp;
	}

	/**
	 * Добавляем новые задачи в список
	 * @param $arUsers
	 * @param $iTaskID
	 * @return bool
	 */
	public static function addNewTaskToList(array $arUsers,$iTaskID)
	{
		foreach($arUsers as $user)
		{
			$key = RedisNotification::getNewTaskKey($user);
			\Yii::$app->redis->sadd($key,$iTaskID);
		}
		return TRUE;
	}

	/**
	 * Удаляем задачу из списка пользователей
	 * @param $arUsers
	 * @param $iTaskID
	 * @return bool
	 */
	public static function removeNewTaskFromList(array $arUsers,$iTaskID)
	{
		foreach($arUsers as $user)
		{
			$key = RedisNotification::getNewTaskKey($user);
			\Yii::$app->redis->srem($key,$iTaskID);
		}
		return TRUE;
	}

	/**
	 * @param array $arUsers
	 * @return bool
	 */
	public static function removeListNewTaskForUsers(array $arUsers)
	{
		foreach($arUsers as $user)
		{
			$key = self::getNewTaskKey($user);
			if(Yii::$app->redis->exists($key))
				Yii::$app->redis->del($key);
		}
		return TRUE;
	}

}