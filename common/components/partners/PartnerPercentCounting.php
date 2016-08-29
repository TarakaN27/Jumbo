<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 19.4.16
 * Time: 14.38
 */

namespace common\components\partners;

use common\components\helpers\CustomDateHelper;
use common\components\helpers\CustomHelper;
use common\models\CUser;
use common\models\EnrollmentRequest;
use common\models\Enrolls;
use common\models\ExchangeCurrencyHistory;
use common\models\PartnerCuserServ;
use common\models\PartnerPurse;
use common\models\PartnerPurseHistory;
use common\models\PartnerSchemes;
use common\models\PartnerSchemesServicesHistory;
use common\models\PaymentCondition;
use common\models\Payments;
use common\models\PaymentsCalculations;
use common\models\Services;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

class PartnerPercentCounting
{

    protected
        $time = NULL,
        $arPartnerIDs = [],
        $exchangeCurrency = [];
    //array for exchange currency history
    public $excludePartnerPeriod = [];

    /**
     * PartnerPercentCounting constructor.
     * @param array $arPartnerIds
     */
    public function __construct(array $arPartnerIds = [])
    {
        $this->arPartnerIDs = $arPartnerIds;
    }

    /**
     * @param null $beginTime
     * @return bool
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function countPercentByMonth($beginTime = NULL)
    {
        $this->time = $time = null === $beginTime ? time() : (is_numeric($beginTime) ? $beginTime : strtotime($beginTime));

        $arPartners = $this->getPartners();                             //Get partners  (array of objects)
        if (empty($arPartners))
            return TRUE;
        $arSchemes = $this->getSchemes($arPartners);                     //Get all schemes for current partner

        foreach ($arPartners as $partner) {
            if (!array_key_exists($partner->id, $this->excludePartnerPeriod) || (array_key_exists($partner->id, $this->excludePartnerPeriod) && $beginTime > $this->excludePartnerPeriod[$partner->id])){
                if (!isset($arSchemes[$partner->partner_scheme]))
                    continue;
                /** @var PartnerSchemes $obScheme */
                $obScheme = $arSchemes[$partner->partner_scheme];           //Scheme for partner

                $arPayTime = $this->getBeginEndPayTime($time, $obScheme->turnover_type);     //begin and end time for payment period

                if (is_null($arPayTime))
                    throw new NotFoundHttpException();

                $arLeads = $this->getPartnerLeads($partner->id);            //Get lead for current partner
                if (count($arLeads) === 0)
                    continue;

                $arPayments = $this->getLeadPayments($arLeads, $arPayTime['begin'], $arPayTime['end']);       //Get lead payments

                $cuserIDs = array_unique(ArrayHelper::getColumn($arLeads, 'cuser_id'));
                $arCuserResident = ArrayHelper::map(CUser::find()->select(['id', 'is_resident'])->where(['id' => $cuserIDs])->all(), 'id', 'is_resident');

                $arPayments = $this->normalizePayments($arPayments, $obScheme);                        //Convert amount to BYR

                $arPartnerServGroups = $this->getPartnerServiceGroups($obScheme,$partner);           //Get partner service group for each service

                $fullAmountByGroup = $this->getPaymentAmount($arPayments, $obScheme->currency_id, $arPartnerServGroups, $arCuserResident, $obScheme, $partner);  //Get full amount at scheme currency

                $percent = $this->getPercent($fullAmountByGroup, $obScheme, $partner);        //Get scheme parameters for services(percent and legal params)

                if (count($percent) === 0)
                    continue;

                if ($obScheme->turnover_type == PartnerSchemes::TURNOVER_TYPE_YEAR)              //unset excess payment, leave payments only for last month
                {
                    $arPayments = $this->normalizePaymentsForYearTurnover($arPayments, $time);
                }
                $this->countPercent($arLeads, $arPayments, $percent, $obScheme, $partner, $arCuserResident);  //Counting percents for partners
            }
        }

        return TRUE;
    }

    /**
     * Из всех платежей за год оставляем только за прошлый месяц, чтобы начислить проыенты
     * @param $arPayments
     * @param $time
     * @return array
     */
    protected function normalizePaymentsForYearTurnover($arPayments, $time)
    {
        $arResult = [];
        $dateMonthAgo = CustomHelper::getDateMinusNumMonth($time, 1);   //1 month ago (timestamp)
        $beginTime = CustomHelper::getBeginMonthTime($dateMonthAgo);   //Month end time (timestamp)
        $endTime = CustomHelper::getEndMonthTime($dateMonthAgo);       //Month begin time (timestamp)

        foreach ($arPayments as $payment) {
            if ($payment['pay_date'] >= $beginTime && $payment['pay_date'] <= $endTime)
                $arResult[] = $payment;
        }
        return $arResult;
    }


    /**
     * @param $time
     * @param $turnOverType
     * @return null
     */
    protected function getBeginEndPayTime($time, $turnOverType)
    {
        $arResult = NULL;
        switch ($turnOverType) {
            case PartnerSchemes::TURNOVER_TYPE_MONTH:
                $dateMonthAgo = CustomHelper::getDateMinusNumMonth($time, 1);           //1 month ago (timestamp)
                $arResult['begin'] = CustomHelper::getBeginMonthTime($dateMonthAgo);    //Month end time (timestamp)
                $arResult['end'] = CustomHelper::getEndMonthTime($dateMonthAgo);        //Month begin time (timestamp)
                break;
            case PartnerSchemes::TURNOVER_TYPE_YEAR:
                $dateYearAgo = CustomHelper::getDateMinusNumYear($time, 1);              //1  year ago
                $dateMonthAgo = CustomHelper::getDateMinusNumMonth($time, 1);           //1 month ago (timestamp)
                $arResult['begin'] = CustomHelper::getBeginMonthTime($dateYearAgo);     //Month end time (timestamp)
                $arResult['end'] = CustomHelper::getEndMonthTime($dateMonthAgo);        //Month begin time (timestamp)
                break;
            default:
                break;
        }
        return $arResult;
    }

    /**
     * Get partners
     * @return mixed
     */
    protected function getPartners()
    {
        $query = CUser::find()
            ->select([
                'id',
                'partner_scheme',
                'archive',
                'partner'
            ])
            ->where('archive = 0 OR archive is NULL')
            ->partner();

        if (!empty($this->arPartnerIDs)) {
            $query->andWhere(['id' => $this->arPartnerIDs]);
        }

        return $query->all();
    }

    /**
     * Get schemes ids for partners
     * @param array $arPartners
     * @return array
     */
    protected function getSchemesIdsByUser(array $arPartners)
    {
        return ArrayHelper::map($arPartners, 'id', 'partner_scheme');
    }

    /**
     * Get partner schemes with services parameters
     * @param array $arPartners
     * @return array
     */
    protected function getSchemes(array $arPartners)
    {
        $arSchemes = array_values($this->getSchemesIdsByUser($arPartners));
        if (empty($arSchemes))
            return [];

        $query = PartnerSchemes::find()
            ->alias('ps')
            ->joinWith('partnerSchemesServices p')
            ->where(['ps.id' => $arSchemes])
            ->all();

        if (empty($query))
            return [];

        return CustomHelper::getMapObjectByAttribute($query, 'id');
    }

    /**
     * @param $iPartnerId
     * @return mixed
     */
    protected function getPartnerLeads($iPartnerId)
    {
        return PartnerCuserServ::find()
            ->select(['service_id', 'cuser_id', 'connect'])
            ->where(['partner_id' => $iPartnerId])
            ->andWhere('archive is NULL or archive = 0')
            ->all();
    }

    /**
     * Get all payments for partner lead
     * @param array $arLeads
     * @param $beginMonth
     * @param $endMonth
     * @return array
     */
    protected function getLeadPayments(array $arLeads, $beginMonth, $endMonth)
    {
        if (count($arLeads) === 0)
            return [];

        $arLeads = array_chunk($arLeads, 10);                        //divide for few query
        $arResult = [];
        foreach ($arLeads as $leads) {
            $queryMain = NULL;
            foreach ($leads as $key => $lead) {
                $query = (new Query())
                    ->from(Payments::tableName())
                    ->select(['id', 'pay_date', 'pay_summ', 'service_id', 'currency_id', 'legal_id', 'cuser_id'])
                    ->where(['cuser_id' => $lead->cuser_id, 'service_id' => $lead->service_id])
                    ->andWhere('pay_date >= :beginMonth' . $key . ' AND pay_date <=:endMonth' . $key . ' AND pay_date >= :connect' . $key)
                    ->params([
                        ':beginMonth' . $key => $beginMonth,
                        ':endMonth' . $key => $endMonth,
                        ':connect' . $key => strtotime($lead->connect)
                    ]);
                if (null == $queryMain)
                    $queryMain = $query;
                else
                    $queryMain->union($query);
            }
            $t = $queryMain->prepare(Yii::$app->db->queryBuilder)->createCommand()->rawSql;
            $arResultTmp = $queryMain->all();
            $arResult = ArrayHelper::merge($arResult, $arResultTmp);
        }
        return $arResult;
    }

    /**
     * Counting payment amount in BUR currency
     * @param $arPayments
     * @param PartnerSchemes $obScheme
     * @return mixed
     */
    protected function normalizePayments($arPayments, PartnerSchemes $obScheme)
    {
        $arResult = [];
        switch ($obScheme->counting_base) {
            case PartnerSchemes::COUNTING_BASE_PAYMENT:
                foreach ($arPayments as &$payment) {
                    $date = date('Y-m-d', $payment['pay_date']);
                    $currency = $payment['currency_id'];
                    if (isset($this->exchangeCurrency[$currency]) && isset($this->exchangeCurrency[$currency][$date])) {
                        $exchRate = $this->exchangeCurrency[$currency][$date];
                    } else {
                        $exchRate = ExchangeCurrencyHistory::getCurrencyInBURForDate($date, $currency);
                        $this->exchangeCurrency[$currency][$date] = $exchRate;
                    }

                    $payment['pay_summ'] = $payment['pay_summ'] * $exchRate;
                }
                $arResult = $arPayments;
                break;
            case PartnerSchemes::COUNTING_BASE_ENROLL:
                $arEnrollServ = $this->getServiceWithEnroll($arPayments);       //get service id , witch allow enrollment
                $arTmpPay = [];

                foreach ($arPayments as $payment) {
                    if (!in_array($payment['service_id'], $arEnrollServ)) {
                        $date = date('Y-m-d', $payment['pay_date']);
                        $currency = $payment['currency_id'];
                        if (isset($this->exchangeCurrency[$currency]) && isset($this->exchangeCurrency[$currency][$date])) {
                            $exchRate = $this->exchangeCurrency[$currency][$date];
                        } else {
                            $exchRate = ExchangeCurrencyHistory::getCurrencyInBURForDate($date, $currency);
                            $this->exchangeCurrency[$currency][$date] = $exchRate;
                        }

                        $payment['pay_summ'] = $payment['pay_summ'] * $exchRate;

                        $arResult[] = $payment;
                    } else {
                        $arTmpPay[] = $payment;
                    }
                }

                if (!empty($arTmpPay)) {
                    $arPayEnroll = $this->getEnrollPayment($arTmpPay);
                    foreach ($arPayEnroll as $enroll)
                        $arResult [] = $enroll;

                }
                break;
            default:
                break;
        }


        return $arResult;
    }

    /**
     * @param array $arPayment
     * @return array
     */
    protected function getEnrollPayment(array $arPayment)
    {
        $arPayIds = ArrayHelper::getColumn($arPayment, 'id');
        $arPayCondLink = $this->getCondByPayId($arPayIds);
        $arPayDate = ArrayHelper::map($arPayment, 'id', 'pay_date');
        $arCondCurr = $this->getConditionsForPayments(array_unique($arPayCondLink));
        $arPayByAnrollAmount = $this->getEnrollAmountForPayment($arPayIds, $arCondCurr, $arPayDate, $arPayCondLink);

        $arResult = [];
        foreach ($arPayment as $pay) {
            if (isset($arPayByAnrollAmount[$pay['id']])) {
                $pay['pay_summ'] = $arPayByAnrollAmount[$pay['id']];
                $arResult[] = $pay;
            }
        }

        return $arResult;
    }

    /**
     * @param array $arPayIds
     * @param $arCondCurr
     * @param $arPayDate
     * @return array
     */
    protected function getEnrollAmountForPayment(array $arPayIds, $arCondCurr, $arPayDate, $arPayCondLink)
    {
        $arEnroll = EnrollmentRequest::find()//Get enrolls request for payment
        ->where([
            'payment_id' => $arPayIds
        ])
            ->andWhere('parent_id IS NULL OR parent_id = 0')
            ->all();

        $arResult = [];

        /** @var EnrollmentRequest $obEnroll */
        foreach ($arEnroll as $obEnroll) {
            $date = $arPayDate[$obEnroll->payment_id];
            $iPayCond = $arPayCondLink[$obEnroll->payment_id];
            $iCurr = $arCondCurr[$iPayCond];

            if (isset($this->exchangeCurrency[$iCurr]) && isset($this->exchangeCurrency[$iCurr][$date])) {
                $exchRate = $this->exchangeCurrency[$iCurr][$date];
            } else {
                $exchRate = ExchangeCurrencyHistory::getCurrencyInBURForDate($date, $iCurr);

                $this->exchangeCurrency[$iCurr][$date] = $exchRate;
            }

            if (isset($arResult[$obEnroll->payment_id])) {
                $arResult[$obEnroll->payment_id] += $exchRate * $obEnroll->amount;
            } else {
                $arResult[$obEnroll->payment_id] = $exchRate * $obEnroll->amount;
            }
        }

        return $arResult;
    }


    /**
     * @param $arCondIds
     * @return mixed
     */
    protected function getConditionsForPayments($arCondIds)
    {
        return PaymentCondition::find()
            ->select(['cond_currency', 'id'])
            ->where(['id' => $arCondIds])
            ->indexBy('id')
            ->column();
    }

    /**
     * @param array $arPayIds
     * @return mixed
     */
    protected function getCondByPayId(array $arPayIds)
    {
        return PaymentsCalculations::find()
            ->select(['pay_cond_id', 'payment_id'])
            ->where(['payment_id' => $arPayIds])
            ->indexBy('payment_id')
            ->column();
    }

    /**
     * @param array $arPayments
     * @return array
     */
    protected function getServiceWithEnroll(array $arPayments)
    {
        $arService = Services::find()
            ->where([
                'id' => array_unique(ArrayHelper::getColumn($arPayments, 'service_id')),
                'allow_enrollment' => Services::YES
            ])
            ->all();
        return $arService ? ArrayHelper::getColumn($arService, 'id') : [];
    }

    /**
     * @param $arPayments
     * @param $currency
     * @return float|int
     */
    protected function getPaymentAmount($arPayments, $currency, $arPartnerServGroups, $arCuserResident, $obScheme,$partner)
    {
        $arServices = $this->getSchemeServices($obScheme,$partner);
        $arServParams = [];
        foreach ($arServices as $serv) {
            $arServParams[$serv->service_id] = [
                'legal' => is_array($serv->legal) ? $serv->legal : NULL
            ];
        }

        $amount = [];

        foreach ($arPayments as $payment) {
            if (!isset($arPartnerServGroups[$payment['service_id']]) || empty($arPartnerServGroups[$payment['service_id']])) {
                continue;
            }

            $group = $arPartnerServGroups[$payment['service_id']];
            $date = date('Y-m-d', $payment['pay_date']);
            if (isset($this->exchangeCurrency[$currency]) && isset($this->exchangeCurrency[$currency][$date])) {
                $exchRate = $this->exchangeCurrency[$currency][$date];
            } else {
                $exchRate = ExchangeCurrencyHistory::getCurrencyInBURForDate($date, $currency);
                $this->exchangeCurrency[$currency][$date] = $exchRate;
            }

            $servId = $payment['service_id'];
            $cuerID = $payment['cuser_id'];

            if (isset($arServParams[$servId]) && array_key_exists($cuerID, $arCuserResident))
                $numAmount = $this->getAmountHelper($arServParams[$payment['service_id']], $payment['pay_summ'], $payment['legal_id'], $arCuserResident[$payment['cuser_id']]);
            else
                $numAmount = $payment['pay_summ'];

            if (isset($amount[$group]))
                $amount[$group] += $numAmount / $exchRate;
            else
                $amount[$group] = $numAmount / $exchRate;
        }

        return $amount;
    }

    /**
     * @param $obScheme
     * @param $time
     * @return null
     */
    protected function getSchemeServices($obScheme, $partner)
    {
        $arServices = NULL;
        if (!CustomDateHelper::isCurrentMonth($this->time)) {
            $selectFlag = PartnerSchemesServicesHistory::find()
                ->select(['id', 'created_at', 'scheme_id'])
                ->where('created_at >= :date', ['date' => CustomHelper::getBeginMonthTime($this->time)])
                ->andWhere(['scheme_id' => $obScheme->id])
                ->orderBy(['created_at' => SORT_ASC])
                ->one();
            if ($selectFlag) {
                $arServices = PartnerSchemesServicesHistory::find()
                    ->where(['scheme_id' => $obScheme->id, 'created_at' => $selectFlag->created_at])
                    ->orderBy(['created_at' => SORT_ASC])
                    ->all();
            }
            unset($selectFlag);
        }
        if (!$arServices)
            $arServices = $obScheme->partnerSchemesServices;

        return $arServices;
    }


    /**
     * Get percent rates for service and groups
     * @param $fullAmount
     * @param $obScheme
     * @return array
     */
    protected function getPercent($fullAmountByGroup, $obScheme, $partner)
    {
        $arServices = $this->getSchemeServices($obScheme,$partner);

        if (empty($arServices))
            return [];

        $arResult = [];
        foreach ($arServices as $serv) {
            if (!isset($fullAmountByGroup[$serv->group_id]))
                continue;

            $fullAmount = $fullAmountByGroup[$serv->group_id];

            $percent = NULL;
            if (is_array($serv->ranges)) {
                foreach ($serv->ranges as $range) {
                    $left = (float)$range['left'];
                    $right = (float)$range['right'];
                    $percentTmp = (float)$range['percent'];

                    if ($left <= $fullAmount && $fullAmount <= $right)
                        $percent = $percentTmp;
                }
            }

            if (null !== $percent && $percent > 0) {
                $arResult[$serv->service_id] = [
                    'percent' => $percent,
                    'legal' => is_array($serv->legal) ? $serv->legal : NULL
                ];
            }
        }

        return $arResult;
    }

    /**
     * @param $arLeads
     * @param $arPayments
     * @param $percent
     * @param $obScheme
     * @param $partner
     * @return bool
     * @throws ServerErrorHttpException
     * @throws \yii\db\Exception
     */
    protected function countPercent($arLeads, $arPayments, $percent, $obScheme, $partner, $arCuserResident)
    {
        $obPurse = PartnerPurse::find()->where(['cuser_id' => $partner->id])->one();
        if (empty($obPurse))
            $obPurse = new PartnerPurse(['cuser_id' => $partner->id]);

        $rows = [];
        $fullAmount = 0;

        foreach ($arPayments as $payment) {

            if (!isset($percent[$payment['service_id']]) || !isset($arCuserResident[$payment['cuser_id']])) {

                continue;
            }

            $amount = $this->getAmount($percent[$payment['service_id']], $payment['pay_summ'], $payment['legal_id'], $arCuserResident[$payment['cuser_id']]);

            $rows [] = [
                '',
                $partner->id,
                $amount,
                PartnerPurseHistory::TYPE_INCOMING,
                $payment['id'],
                '',
                time(),
                time(),
                $percent[$payment['service_id']]['percent'],
                $payment['pay_date'],
            ];

            $fullAmount += $amount;
        }
        $transaction = \Yii::$app->db->beginTransaction();
        $historyModel = new PartnerPurseHistory();                                          //wrote history, make batch insert query
        if (count($rows) > 0)
            if (!Yii::$app->db->createCommand()
                ->batchInsert(PartnerPurseHistory::tableName(), $historyModel->attributes(), $rows)
                ->execute()
            ) {
                $transaction->rollBack();
                throw new ServerErrorHttpException();
            }

        if (empty($obPurse->amount))
            $obPurse->amount = $fullAmount;
        else
            $obPurse->amount += $fullAmount;

        if (!$obPurse->save()) {
            $transaction->rollBack();
            throw new ServerErrorHttpException();
        }

        $transaction->commit();
        return TRUE;
    }

    /**
     * @param $params
     * @param $amount
     * @param $legalID
     * @param $isResident
     * @return float
     */
    protected function getAmount($params, $amount, $legalID, $isResident)
    {
        $amount = $this->getAmountHelper($params, $amount, $legalID, $isResident);
        return $amount * ($params['percent'] / 100);
    }

    /**
     * @param $params
     * @param $amount
     * @param $legalID
     * @param $isResident
     * @return float
     */
    protected function getAmountHelper($params, $amount, $legalID, $isResident)
    {
        if (is_array($params['legal']) &&  //проверяем не указано ли для Юр. лица отнимать НАЛОГ от платежа
            isset($params['legal'][$legalID]) &&
            isset($params['legal'][$legalID]['deduct']) &&
            isset($params['legal'][$legalID]['deduct']) == 1
        ) {
            $key = $isResident ? 'res' : 'not_res';
            if (isset($params['legal'][$legalID][$key])) {
                $tax = NULL;
                if (isset($params['legal'][$legalID][$key . '_tax']) && is_numeric($params['legal'][$legalID][$key . '_tax']))
                    $tax = $params['legal'][$legalID][$key . '_tax'];
                $amount = CustomHelper::getVatMountByAmount($amount, $tax); //отнимем от суммы платежа налог
            }
        }
        return $amount;
    }


    /**
     * @param $obScheme
     * @return array
     */
    protected function getPartnerServiceGroups($obScheme,$partner)
    {
        $arServices = $this->getSchemeServices($obScheme,$partner);
        if (empty($arServices))
            return [];

        return ArrayHelper::map($arServices, 'service_id', 'group_id');
    }

}