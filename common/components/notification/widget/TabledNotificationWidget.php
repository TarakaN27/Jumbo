<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 13.1.16
 * Time: 15.22
 */

namespace common\components\notification\widget;


use common\components\notification\TabledNotification;
use common\components\notification\widget\assets\TabledNotificationWidgetAssets;
use common\components\notification\widget\assets\TNWAssets;
use yii\base\Widget;

class TabledNotificationWidget extends Widget
{
	public function run()
	{
		$this->registerAssets1();
		return $this->render('tabled_notification',[]);
	}

	/**
	 * Регистрируем все JS & CSS файлы
	 */
	public function registerAssets1()
	{
		$host = \Yii::$app->getUrlManager()->getHostInfo();
		$view = $this->getView();
		TNWAssets::register($view);
		$view->registerJs("
			var
				host = '".$host."',
				wmu = ".\Yii::$app->user->id.",
				wm_chanel = '".TabledNotification::$chanel."',
				TYPE_BROADCAST = '".TabledNotification::TYPE_BROADCAST."',
				TYPE_PRIVATE = '".TabledNotification::TYPE_PRIVATE."';
		",$view::POS_HEAD);
	}
}