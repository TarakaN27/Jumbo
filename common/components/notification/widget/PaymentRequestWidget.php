<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 4.2.16
 * Time: 12.18
 */

namespace common\components\notification\widget;


use common\components\notification\RedisNotification;
use yii\base\Widget;


class PaymentRequestWidget extends Widget
{
	public function run()
	{
		$count = RedisNotification::countNewPaymentRequest(\Yii::$app->user->id);
		return $this->render('payment_new',[
			'count' => (int)$count
		]);
	}
}