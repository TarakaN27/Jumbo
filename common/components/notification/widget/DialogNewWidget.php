<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 15.1.16
 * Time: 15.36
 */

namespace common\components\notification\widget;


use common\components\notification\RedisNotification;
use yii\base\Widget;

class DialogNewWidget extends Widget
{

	public function run()
	{
		$count = RedisNotification::countNewDialog(\Yii::$app->user->id);
		return $this->render('dialog_new_widget',[
			'count' => $count
		]);
	}

}