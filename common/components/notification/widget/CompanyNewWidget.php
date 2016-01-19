<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 19.1.16
 * Time: 10.13
 */

namespace common\components\notification\widget;


use yii\base\Widget;
use common\components\notification\RedisNotification;

class CompanyNewWidget extends Widget
{
	public function run()
	{
		$count = RedisNotification::countNewCompany(\Yii::$app->user->id);
		return $this->render('company_new',[
			'count' => (int)$count
		]);
	}
}