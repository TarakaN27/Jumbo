<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 19.1.16
 * Time: 12.52
 */

namespace common\components\notification\widget;


use yii\base\Widget;
use common\components\notification\RedisNotification;

class ContactNewWidget extends Widget
{
	public function run()
	{
		$count = RedisNotification::countNewContact(\Yii::$app->user->id);
		return $this->render('contact_new',[
			'count' => (int)$count
		]);
	}

}