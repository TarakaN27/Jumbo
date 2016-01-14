<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 14.1.16
 * Time: 10.10
 */

namespace common\components\notification\widget;


use common\components\notification\RedisNotification;
use yii\base\Widget;

class TaskNewWidget extends Widget
{
	public function run()
	{
		$count = RedisNotification::getNewTaskCount(\Yii::$app->user->id);
		return $this->render('task_new',[
			'count' => (int)$count
		]);
	}
}