<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 22.6.16
 * Time: 16.59
 */
namespace common\components\payment;

use common\models\PartnerCuserServ;
use common\models\Payments;
use common\models\RecalculatePartner;
use yii\base\Behavior;
use common\components\helpers\CustomHelper;
use yii\web\ServerErrorHttpException;

class PaymentRecalculatePartnerPercentBehavior extends Behavior
{
    /**
     * @return array
     */
    public function events()
    {
        return [
            Payments::EVENT_AFTER_INSERT => 'afterInsert',
            Payments::EVENT_AFTER_DELETE => 'afterDelete',
            Payments::EVENT_AFTER_UPDATE => 'afterUpdate'
        ];
    }

    /**
     * @throws ServerErrorHttpException
     */
    public function afterInsert()
    {
        if($this->checkTime())              //if payment time less or equal to end last month time
        {
            $this->isPartnerLead(true);                         //check if cuser is a lead of partner, than add flag for recalculate partner percent
        }
    }

    /**
     * @throws ServerErrorHttpException
     */
    public function afterDelete()
    {
        if($this->checkTime())
        {
            $this->isPartnerLead();
        }
    }

    /**
     * @throws ServerErrorHttpException
     */
    public function afterUpdate()
    {
        if($this->checkTime())
        {
            $this->isPartnerLead();
        }
    }

    /**
     * @return bool
     */
    protected function checkTime()
    {
        /** @var Payments $model */
        $model = $this->owner;          //payments model
        $iEndPrevMonth = CustomHelper::getDateMinusNumMonth(time(),1);      //get time begin current date minus 1 month
        $iEndPrevMonth = CustomHelper::getEndMonthTime($iEndPrevMonth);     //get end month time of last month
        return $model->pay_date <= $iEndPrevMonth;
    }


    /**
     * @return bool
     * @throws ServerErrorHttpException
     */
    protected function isPartnerLead($bSetPayment = false)
    {
        /** @var Payments $model */
        $model = $this->owner;
        /** @var PartnerCuserServ $obPartnerServLink */
        $obPartnerServLink = PartnerCuserServ::find()
            ->where([
                'cuser_id' => $model->cuser_id,
                'service_id' => $model->service_id,
                'archive' => PartnerCuserServ::NO
            ])
            ->one();

        if($obPartnerServLink && strtotime($obPartnerServLink->connect) <= $model->pay_date)
        {
            $obRecal = new RecalculatePartner();
            $obRecal->cuser_id = $obPartnerServLink->partner_id;
            if($bSetPayment)
                $obRecal->payment_id = $model->id;

            $obRecal->service_id = $model->service_id;
            $obRecal->begin_date = date('Y-m-d',$model->pay_date);
            if(!$obRecal->save())
                throw new ServerErrorHttpException();
        }

        return TRUE;
    }
}