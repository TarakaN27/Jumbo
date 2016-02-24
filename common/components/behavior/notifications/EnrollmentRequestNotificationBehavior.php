<?php
/**
 * Created by PhpStorm.
 * Corp: ZM_TEAM
 * User: E. Motuz
 * Date: 2/24/16
 * Time: 12:27 PM
 */

namespace common\components\behavior\notifications;


use common\components\notification\RedisNotification;
use common\components\notification\TabledNotification;
use yii\base\Behavior;
use common\models\PromisedPayment;
use common\models\EnrollmentRequest;
use yii\helpers\Html;

class EnrollmentRequestNotificationBehavior extends Behavior {

    /**
     * Назначаем событиям обработчики
     * @return array
     */
    public function events()
    {
        return [
            PromisedPayment::EVENT_AFTER_INSERT => 'afterInsert',
            PromisedPayment::EVENT_VIEWED => 'viewed'
        ];
    }

    /**
     *
     */
    public function afterInsert()
    {
        /** @var EnrollmentRequest $model */
        $model = $this->owner;
        RedisNotification::addNewEnrollmentRequestToListForUsers([$model->assigned_id],$model->id);

        $theme = \Yii::t('app/book','New enrollment request');
        $body = Html::a(
            \Yii::t('app/book','Enrollment request for {company} by {service}',[
                'company' => is_object($obCuser = $model->cuser) ? $obCuser->getInfo() : '-',
                'service' => is_object($obSrv = $model->service) ? $obSrv->name : '-'
            ]),['/bookkeeping/enrollment-request/index','view' => $model->id],[
            'target' => '_blank'
        ]);

        TabledNotification::addMessage(
            $theme,
            $body,
            TabledNotification::TYPE_PRIVATE,
            TabledNotification::NOTIF_TYPE_INFO,
            [$model->assigned_id]
        );
    }

    /**
     *
     */
    public function viewed()
    {
        /** @var EnrollmentRequest $model */
        $model = $this->owner;
        RedisNotification::removeEnrollmentRequestFromListForUser(\Yii::$app->user->id,$model->id);
    }

} 