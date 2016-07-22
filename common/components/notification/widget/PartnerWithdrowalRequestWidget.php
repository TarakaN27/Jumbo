<?php
/**
 * Created by PhpStorm.
 * Corp: ZM_TEAM
 * User: E. Motuz
 * Date: 2/24/16
 * Time: 1:27 PM
 */

namespace common\components\notification\widget;


use backend\models\BUser;
use common\models\PartnerWBookkeeperRequest;
use yii\base\Widget;
use Yii;

class PartnerWithdrowalRequestWidget extends Widget{

    public function run()
    {
        $count = 0;
        if(Yii::$app->user->identity->role == BUser::ROLE_BOOKKEEPER){
            $count = PartnerWBookkeeperRequest::find()->where(['buser_id'=>Yii::$app->user->id, 'status'=>PartnerWBookkeeperRequest::STATUS_NEW])->count();
        }
        return $this->render('enrollment_request_new',[
            'count' => (int)$count
        ]);
    }

} 