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
		return self::CONTACT_KEY.':id:'.$iUserID;
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

	/**
	 * есть ли в списке элемент
	 * @param $key
	 * @param $value
	 * @return mixed
	 */
	public static function isValueInList($key,$value)
	{
		return Yii::$app->redis->sismember($key,$value);
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
	/**
	 * @param $arUsers
	 * @param $value
	 * @return bool
	 */
	public static function addNewDialogToListForUsers($arUsers,$value)
	{
		return static::addItemToListForUsers($arUsers,$value,'getDialogKey');
	}

	/**
	 * @param $arUsers
	 * @param $value
	 * @return bool
	 */
	public static function removeDialogFromListForUsers($arUsers,$value)
	{
		return static::removeItemFromListForUsers($arUsers,$value,'getDialogKey');
	}

	/**
	 * @param $iUserID
	 * @param $value
	 * @return bool
	 */
	public static function removeDialogFromListForUser($iUserID,$value)
	{
		$key = self::getDialogKey($iUserID);   //получаем ключ
		return self::removeViewed($key,$value);
	}

	/**
	 * @param $iUserID
	 * @return array
	 */
	public static function getDialogListForUser($iUserID)
	{
		$key = static::getDialogKey($iUserID);
		return static::itemList($key);
	}

	/**
	 * @param $iUserID
	 * @param $value
	 * @return mixed
	 */
	public static function isDialogInList($iUserID,$value)
	{
		$key = static::getDialogKey($iUserID);
		return static::isValueInList($key,$value);
	}

	/**
	 * @param $iUserID
	 * @return int
	 */
	public static function countNewDialog($iUserID)
	{
		$key = static::getDialogKey($iUserID);
		return static::countItem($key);
	}

	/**
	 * @param $arUsers
	 * @return bool
	 */
	public static function removeDialogListForUsers($arUsers)
	{
		return static::removeListForUsers($arUsers,'getDialogKey');
	}

	/********************COMPANY*********************************/

	/**
	 * @param $arUsers
	 * @param $value
	 * @return bool
	 */
	public static function addNewCompanyToListForUsers($arUsers,$value)
	{
		return static::addItemToListForUsers($arUsers,$value,'getNewCompanyKey');
	}

	/**
	 * @param $arUsers
	 * @param $value
	 * @return bool
	 */
	public static function removeCompanyFromListForUsers($arUsers,$value)
	{
		return static::removeItemFromListForUsers($arUsers,$value,'getNewCompanyKey');
	}

	/**
	 * @param $iUserID
	 * @param $value
	 * @return bool
	 */
	public static function removeCompanyFromListForUser($iUserID,$value)
	{
		$key = self::getNewCompanyKey($iUserID);   //получаем ключ
		return self::removeViewed($key,$value);
	}

	/**
	 * @param $iUserID
	 * @return array
	 */
	public static function getCompanyListForUser($iUserID)
	{
		$key = static::getNewCompanyKey($iUserID);
		return static::itemList($key);
	}

	/**
	 * @param $iUserID
	 * @param $value
	 * @return mixed
	 */
	public static function isCompanyInList($iUserID,$value)
	{
		$key = static::getNewCompanyKey($iUserID);
		return static::isValueInList($key,$value);
	}

	/**
	 * @param $iUserID
	 * @return int
	 */
	public static function countNewCompany($iUserID)
	{
		$key = static::getNewCompanyKey($iUserID);
		return static::countItem($key);
	}

	/**
	 * @param $arUsers
	 * @return bool
	 */
	public static function removeCompanyListForUsers($arUsers)
	{
		return static::removeListForUsers($arUsers,'getNewCompanyKey');
	}

	/****************CONTACT ***************************************/
	/**
	 * @param $arUsers
	 * @param $value
	 * @return bool
	 */
	public static function addNewContactToListForUsers($arUsers,$value)
	{
		return static::addItemToListForUsers($arUsers,$value,'getNewContactKey');
	}

	/**
	 * @param $arUsers
	 * @param $value
	 * @return bool
	 */
	public static function removeContactFromListForUsers($arUsers,$value)
	{
		return static::removeItemFromListForUsers($arUsers,$value,'getNewContactKey');
	}

	/**
	 * @param $iUserID
	 * @param $value
	 * @return bool
	 */
	public static function removeContactFromListForUser($iUserID,$value)
	{
		$key = self::getNewContactKey($iUserID);   //получаем ключ
		return self::removeViewed($key,$value);
	}

	/**
	 * @param $iUserID
	 * @return array
	 */
	public static function getContactListForUser($iUserID)
	{
		$key = static::getNewContactKey($iUserID);
		return static::itemList($key);
	}

	/**
	 * @param $iUserID
	 * @param $value
	 * @return mixed
	 */
	public static function isContactInList($iUserID,$value)
	{
		$key = static::getNewContactKey($iUserID);
		return static::isValueInList($key,$value);
	}

	/**
	 * @param $iUserID
	 * @return int
	 */
	public static function countNewContact($iUserID)
	{
		$key = static::getNewContactKey($iUserID);
		return static::countItem($key);
	}

	/**
	 * @param $arUsers
	 * @return bool
	 */
	public static function removeContactListForUsers($arUsers)
	{
		return static::removeListForUsers($arUsers,'getNewContactKey');
	}

}