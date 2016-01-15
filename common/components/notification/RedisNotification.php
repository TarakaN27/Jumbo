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
		DIALOG_KEY = 'udialog',
		MSG_KEY = 'umsg',
		FEED_KEY = 'ufeed',
		CONTACT_KEY = 'ucontact',
		COMPANY_KEY = 'ucompany',
		TASK_KEY = 'utask';

	/********************************КЛЮЧИ для redis**********************************/

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
	 * @param $iUserID
	 * @return string
	 */
	public static function getDialogKey($iUserID)
	{
		return self::DIALOG_KEY.':id:'.$iUserID;
	}

	/**************************end ключи для redis **********************/
	/************************** общие методы ***************************/
	/**
	 * Удаляем элемент из списка
	 * @param $key
	 * @param $value
	 * @return bool
	 */
	public static function removeViewed($key,$value)
	{
		if(Yii::$app->redis->sismember($key,$value))
			return Yii::$app->redis->srem($key,$value); //удаляем из списка
		return TRUE;
	}

	/**
	 * Кол-во элементов в списке
	 * @param $key
	 * @return int
	 */
	public static function countItem($key)
	{
		$count = \Yii::$app->redis->scard($key);
		return (int)$count;
	}

	/**
	 * Элементы списка
	 * @param $key
	 * @return array
	 */
	public static function itemList($key)
	{
		$tmp = Yii::$app->redis->smembers($key);
		return empty($tmp) ? [] : $tmp;
	}

	/**
	 * Добавление элемента списка для пользователй
	 * @param array $arUsers
	 * @param $value
	 * @param $funcName
	 * @return bool
	 */
	public static function addItemToListForUsers(array $arUsers,$value,$funcName)
	{
		foreach($arUsers as $user)
		{
			$key = RedisNotification::$funcName($user);
			\Yii::$app->redis->sadd($key,$value);
		}
		return TRUE;
	}

	/**
	 * Удаление элемента списка для пользователей
	 * @param array $arUsers
	 * @param $value
	 * @param $funcName
	 * @return bool
	 */
	public static function removeItemFromListForUsers(array $arUsers,$value,$funcName)
	{
		foreach($arUsers as $user)
		{
			$key = RedisNotification::$funcName($user);
			\Yii::$app->redis->srem($key,$value);
		}
		return TRUE;
	}

	/**
	 * Удаление всего списка для пользователей
	 * @param array $arUsers
	 * @param $funcName
	 * @return bool
	 */
	public static function removeListForUsers(array $arUsers,$funcName)
	{
		foreach($arUsers as $user)
		{
			$key = self::$funcName($user);
			if(Yii::$app->redis->exists($key))
				Yii::$app->redis->del($key);
		}
		return TRUE;
	}

	/*********************************END общие методы*******************************************/

	/********************************* задачи ***************************************/
	/**
	 * Удаляем просмотренную задачу из списка новых
	 * @param $iUserID
	 * @param $iTaskID
	 * @return bool
	 */
	public static function removeViewedNewTask($iUserID,$iTaskID)
	{
		$key = self::getNewTaskKey($iUserID);   //получаем ключ для новой задачи пользователя
		return self::removeViewed($key,$iTaskID);
	}

	/**
	 * Считаем сколько новых задач у нас в списке
	 * @param $iUserID
	 * @return int
	 */
	public static function getNewTaskCount($iUserID)
	{
		$key = RedisNotification::getNewTaskKey($iUserID);
		return self::countItem($key);
	}

	/**
	 * Получаем список новых задач
	 * @param $iUserID
	 * @return array
	 */
	public static function getNewTaskList($iUserID)
	{
		$key = self::getNewTaskKey($iUserID);
		return self::itemList($key);
	}

	/**
	 * Добавляем новые задачи в список
	 * @param $arUsers
	 * @param $iTaskID
	 * @return bool
	 */
	public static function addNewTaskToList(array $arUsers,$iTaskID)
	{
		return static::addItemToListForUsers($arUsers,$iTaskID,'getNewTaskKey');
	}

	/**
	 * Удаляем задачу из списка пользователей
	 * @param $arUsers
	 * @param $iTaskID
	 * @return bool
	 */
	public static function removeNewTaskFromList(array $arUsers,$iTaskID)
	{
		return static::removeItemFromListForUsers($arUsers,$iTaskID,'getNewTaskKey');
	}

	/**
	 * @param array $arUsers
	 * @return bool
	 */
	public static function removeListNewTaskForUsers(array $arUsers)
	{
		return static::removeListForUsers($arUsers,'getNewTaskKey');
	}

	/*************************************end задачи ***********************************/
	public static function addNewDialogToListForUsers($arUsers,$value)
	{
		return static::addItemToListForUsers($arUsers,$value,'getDialogKey');
	}

}