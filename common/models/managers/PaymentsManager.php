<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 24.3.16
 * Time: 11.13
 */

namespace common\models\managers;


use common\components\helpers\CustomHelper;
use common\models\ActToPayments;
use common\models\CuserToGroup;
use common\models\EnrollmentRequest;
use common\models\PaymentRequest;
use common\models\Payments;
use common\models\PaymentsSale;
use common\models\Services;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use common\models\CUser;
use Yii;

class PaymentsManager extends Payments
{
    /**
     * Проверка является ли платеж продажей
     * @param $iServID
     * @param $iCUserID
     * @param $payDate
     * @return bool
     * @throws NotFoundHttpException
     */
    public static function isSale($iCUserID, $payDate, $iPRequest = NULL)
    {
        $inActivePeriod = (int)Yii::$app->config->get('c_inactivity', 0);  //период бездействия в месяцах

        if ($inActivePeriod <= 0)    //не задан период бездействия, вернем FALSE
            return FALSE;
        $beginDate = CustomHelper::getBeginDayTime($payDate);
        $saleDate = CustomHelper::getDateMinusNumMonth($beginDate, $inActivePeriod);
        $cUser = CUser::findOne($iCUserID);
        $arCuser = false;
        //если у контрагента не стоит дата продажи поищем дату в группе компаний
        if (!$cUser->sale_date) {
            $arCuser = self::getUserByGroup($iCUserID);
            $cUserTemp = CUser::find()->where(['id'=>$arCuser])->andWhere(['not',['sale_date'=>null]])->one();
            if ($cUserTemp->sale_date)
                $cUser->sale_date = $cUserTemp->sale_date;
        }
        if ($cUser->sale_date) {
            if ($cUser->sale_date > $saleDate) {
                return false;
            }
            if ($arCuser === false)
                $arCuser = self::getUserByGroup($iCUserID);
            $query = Payments::find()//проверяем , если не было платежей за период бездействия
            ->where(['cuser_id' => $arCuser])
                ->andWhere('pay_date >= :beginDate')
                ->params([':beginDate' => $beginDate])
                ->limit(1);
            return !$query->exists();
        } else
            return true;

    }


    /**
     * @param $iServID
     * @param $iCUserID
     * @param $payDate
     * @param bool|FALSE $useGroup
     * @return null
     */
    public static function getPaymentMonth($iServID, $iCUserID, $payDate, $useGroup = FALSE)
    {
        $arCuser = self::getUserByGroup($iCUserID); //получаем пользователей группы компаний
        $obPaymentSale = PaymentsSale::find()//находим первую продажу услуги компании или группе компаний
        ->where(['cuser_id' => $arCuser, 'service_id' => $iServID])
            ->orderBy(['sale_date' => SORT_DESC])
            ->one();;

        if (!$obPaymentSale)     //нет продажи, не сможем определить кол-во месяцев со дня продажи
            return NULL;

        //$obService = Services::find()->select(['id','c_inactivity'])->where()
        /**
         * $obPayment = Payments::find()   //находим продажу
         * ->select(['id','pay_date'])
         * ->where('pay_date >= :beginDate')
         * ->params([':beginDate' => $obPaymentSale->sale_date])
         * ->orderBy(['pay_date' =>SORT_ASC])
         * ->one();
         *
         * if(!$obPayment)
         * return NULL;
         */

        if ($payDate < $obPaymentSale->sale_date)
            return NULL;

        $date1 = new \DateTime();
        $date1->setTimestamp($payDate);
        $date2 = new \DateTime();
        $date2->setTimestamp($obPaymentSale->sale_date);
        $interval = $date1->diff($date2);
        unset($date1, $date2);
        return $interval->m;    //вренем разницу в месяцах между двумя датами
    }

    /**
     * @param $iCUserID
     * @return array
     */
    public static function getUserByGroup($iCUserID)
    {
        $arCuser = [$iCUserID];     //находим всех клиентов группы, если клиент в группе, иначе указывем только клиента
        $tmpGroup = CuserToGroup::find()
            ->select(['group_id'])
            ->where(['cuser_id' => $iCUserID])
            ->all();

        $arGroup = [];
        foreach ($tmpGroup as $t)
            $arGroup [] = $t->group_id;
        if (!empty($arGroup)) {
            $tmp = CuserToGroup::find()
                ->select(['cuser_id'])
                ->where(['group_id' => $arGroup])
                ->all();

            if (!empty($tmp))
                foreach ($tmp as $t)
                    $arCuser [] = $t->cuser_id;
        }
        return array_unique($arCuser);
    }

    /**
     * @param $iCUser
     * @param $iLegalPerson
     * @return mixed
     */
    public static function getPaymentsForAct($iCUser, $iLegalPerson)
    {
        $arPayments = Payments::find()
            ->select(['cuser_id', 'pay_date', 'pay_summ', 'currency_id', 'service_id', 'legal_id', 'id', 'payment_order', 'hide_act_payment'])
            ->where([
                'cuser_id' => $iCUser,
                'legal_id' => $iLegalPerson,
                'act_close' => self::NO
            ])
            ->with('currency', 'service')
            ->all();
        if (!$arPayments)
            return [];

        $arActPayment = ActToPayments::getRecordsByPaymentsId(ArrayHelper::getColumn($arPayments, 'id'));
        if ($arPayments)
            /** @var Payments $obPay */
            foreach ($arPayments as &$obPay) {
                if (isset($arActPayment[$obPay->id])) {
                    foreach ($arActPayment[$obPay->id] as $actPay)
                        $obPay->actAmount += $actPay->amount;
                }
            }

        $arServices = ArrayHelper::getColumn($arPayments, 'service_id');                //service ids
        $arEnrollServ = ArrayHelper::getColumn(
            Services::find()->select(['id', 'allow_enrollment'])->where(['id' => $arServices, 'allow_enrollment' => Services::YES])->all(),
            'id'
        );

        $arPayNeedCheck = [];                //платежи для проверки
        foreach ($arPayments as $obPayTmp)
            if ($arEnrollServ && in_array($obPayTmp->service_id, $arEnrollServ))
                $arPayNeedCheck[] = $obPayTmp->id;
        $arPayNeedCheck = array_unique($arPayNeedCheck);
        $arUnEnroll = [];
        if ($arPayNeedCheck) {
            $arUnEnroll = EnrollmentRequest::find()//не зачисленные платежи.
            ->select(['id', 'payment_id', 'parent_id', 'status', 'part_enroll'])
                ->where([
                    'payment_id' => $arPayNeedCheck,
                    'status' => EnrollmentRequest::STATUS_NEW
                ])
                ->all();

            $arUnEnroll = CustomHelper::getMapObjectByAttribute($arUnEnroll, 'payment_id');
        }

        /** @var Payments $pay */
        foreach ($arPayments as &$pay) {
            if (in_array($pay->id, $arPayNeedCheck)) {
                if (isset($arUnEnroll[$pay->id])) {
                    $tmpEnroll = $arUnEnroll[$pay->id];
                    if (!empty($tmpEnroll->parent_id))
                        $pay->enrollStatus = Payments::ENROLL_PART;
                    else
                        $pay->enrollStatus = Payments::ENROLL_NO;
                } else {
                    $pay->enrollStatus = Payments::ENROLL_YES;
                }
            }
        }

        return $arPayments;
    }

}