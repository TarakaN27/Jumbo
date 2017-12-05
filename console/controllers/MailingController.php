<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 11.4.16
 * Time: 12.16
 */

namespace console\controllers;

use common\models\PaymentRequest;
use console\components\AbstractConsoleController;
use Yii;

class MailingController extends AbstractConsoleController
{
    public function actionSendMailToCuser($offset = 0){
        $clients = PaymentRequest::find()->where(['legal_id'=>3])->groupBy(['cntr_id'])->all();
        foreach($clients as $key=>$item) {
            echo $key."\n";
            if($key >= $offset) {
                if ($item->cuser->requisites && $item->cuser->requisites->c_email && !strpos($item->cuser->requisites->c_email, "webmart.by")) {
                    $attach = Yii::getAlias("@common") . '/upload/Webmart_notice.pdf';
                    Yii::$app->salesMailer->compose()
                        ->setFrom([Yii::$app->params['salesEmail'] => Yii::$app->params['salesName']])//от кого уходит письмо
                        ->setTo($item->cuser->requisites->c_email)
                        ->setBcc(\Yii::$app->params['salesEmail'])//скрытая копия
                        ->setSubject("УВЕДОМЛЕНИЕ Об изменении банковских реквизитов")//тема письма
                        ->attach($attach)//прикрепляем акт к письму
                        ->send();
                }
            }
        }
    }
}