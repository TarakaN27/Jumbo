<?php
/**
 * Created by PhpStorm.
 * Corp: ZM_TEAM
 * User: E. Motuz
 * Date: 2/24/16
 * Time: 1:27 PM
 */

namespace common\components\notification\widget;


use yii\base\Widget;
use common\components\notification\RedisNotification;

class EnrollmentRequestWidget extends Widget{

    public function run()
    {
        $count = RedisNotification::countNewEnrollmentRequest(\Yii::$app->user->id);
        return $this->render('enrollment_request_new',[
            'count' => (int)$count
        ]);
    }

} 