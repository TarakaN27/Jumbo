<?php
/**
 * Created by PhpStorm.
 * Corp: ZM_TEAM
 * User: E. Motuz
 * Date: 2/24/16
 * Time: 1:47 PM
 */

namespace common\components\payment;


use common\models\EnrollmentRequest;
use common\models\ExchangeCurrencyHistory;
use common\models\PaymentCondition;
use common\models\Payments;
use common\models\PaymentsCalculations;
use yii\base\Behavior;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use yii\base\InvalidParamException;

class PaymentEnrollmentBehavior extends Behavior{

    public function events()
    {
        return [
            Payments::EVENT_SAVE_DONE => 'afterInsert'
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
        /** @var Payments $model */
        $model = $this->owner;

        $obSrv = $model->service;
        if(empty($obSrv))
            throw new NotFoundHttpException();

        if(!$obSrv->allow_enrollment)
            return TRUE;

        if(!$obSrv->b_user_enroll)
            throw new InvalidParamException('You must set responsibility for enrollment at service');

        $obCond = $model->condition;
        $obCalc = $model->calculate;

        if(empty($obCond) || empty($obCalc))
            throw new NotFoundHttpException();


        $obEnrollReq = new EnrollmentRequest();
        $obEnrollReq->amount = $this->countAmoutForEnrollment($model,$obCond,$obCalc);
        $obEnrollReq->service_id = $obSrv->id;
        $obEnrollReq->assigned_id = $obSrv->b_user_enroll;
        $obEnrollReq->payment_id = $model->id;
        $obEnrollReq->cuser_id = $model->cuser_id;
        $obEnrollReq->pay_amount = $model->pay_summ;
        $obEnrollReq->pay_currency = $model->currency_id;
        $obEnrollReq->pay_date = $model->pay_date;
        $obEnrollReq->status = EnrollmentRequest::STATUS_NEW;
        if(!$obEnrollReq->save())
        {
            throw new ServerErrorHttpException('Error. Save record');
        }
    }


    protected function countAmoutForEnrollment(Payments $model,PaymentCondition $obCond,PaymentsCalculations $obCalc)
    {
        $curr = ExchangeCurrencyHistory::getCurrencyInBURForDate(date('Y-m-d',$model->pay_date),$obCond->cond_currency);
        return round($obCalc->production/$curr,2);
    }

} 