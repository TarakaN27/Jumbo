<?php
/**
 * Created by PhpStorm.
 * Corp: ZM_TEAM
 * User: E. Motuz
 * Date: 2/24/16
 * Time: 12:17 PM
 */

namespace common\components\behavior\PromisedPayment;


use common\models\EnrollmentRequest;
use common\models\PromisedPayment;
use yii\base\Behavior;
use yii\base\InvalidParamException;
use yii\web\NotFoundHttpException;
use common\models\Services;
use yii\web\ServerErrorHttpException;

class PromisedpaymentBehavior extends Behavior {


    /**
     * Назначаем событиям обработчики
     * @return array
     */
    public function events()
    {
        return [
            PromisedPayment::EVENT_AFTER_INSERT => 'afterInsert'
        ];
    }

    /**
     * @return bool
     * @throws \yii\base\InvalidParamException
     * @throws \yii\web\NotFoundHttpException
     * @throws \yii\web\ServerErrorHttpException
     */
    public function afterInsert()
    {
        /** @var Promisedpayment $model */
        $model = $this->owner;
        /** @var Services $obService */
        $obService = $model->service;
        if(!$obService)
            throw new NotFoundHttpException('Service not found');

        if(empty($obService->b_user_enroll))
            throw new InvalidParamException('You must set responsibility for enrollment at service');

        $obReq = new EnrollmentRequest();
        $obReq->service_id = $obService->id;
        $obReq->assigned_id = $obService->b_user_enroll;
        $obReq->amount = $model->amount;
        $obReq->cuser_id = $model->cuser_id;
        $obReq->pr_payment_id = $model->id;
        $obReq->status = EnrollmentRequest::STATUS_NEW;
        if(!$obReq->save())
        {
            throw new ServerErrorHttpException('Error. Save record');
        }

        return TRUE;
    }

} 