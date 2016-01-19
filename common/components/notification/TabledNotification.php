<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 13.1.16
 * Time: 15.15
 * Компонент для добавления real time сообщений
 *
 *
$name = 'TEST TITLE';
$message = 'test messages';
TabledNotification::addMessage($name,$message,TabledNotification::TYPE_BROADCAST); // широковещательное сообщение
TabledNotification::addMessage($name,$message,TabledNotification::TYPE_PRIVATE,TabledNotification::NOTIF_TYPE_ERROR,['3']
); //индивидуальное сообщение
 *
 */

namespace common\components\notification;

use Yii;
use yii\helpers\Json;

class TabledNotification
{
	CONST   //тип сообщения
		TYPE_BROADCAST = 1,
		TYPE_PRIVATE = 2;

	CONST   //типы оповещений
		NOTIF_TYPE_ERROR = 'error',
		NOTIF_TYPE_WARNING = 'warning',
		NOTIF_TYPE_INFO = 'info',
		NOTIF_TYPE_SUCCESS = 'success';

	public static //канал для сообщений
		$chanel = 'notification';

	/**
	 * @param $title
	 * @param $body
	 * @param array $wmu
	 * @return mixed
	 */
	public static function addMessage($title,$body,$type=self::TYPE_BROADCAST,$ntfType=self::NOTIF_TYPE_INFO,$wmu = [])
	{
		return Yii::$app->redis->executeCommand('PUBLISH', [
			'channel' => static::$chanel,
			'message' => Json::encode([
				'name' => $title,
				'type' => $type,
				'ntf_type' => $ntfType,
				'message' => $body,
				'wmu' => $wmu
			])
		]);
	}

}