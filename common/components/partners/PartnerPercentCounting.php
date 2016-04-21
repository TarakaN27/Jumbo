<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 19.4.16
 * Time: 14.38
 */

namespace common\components\partners;

use common\components\helpers\CustomHelper;
use common\models\CUser;
use common\models\ExchangeCurrencyHistory;
use common\models\PartnerCuserServ;
use common\models\PartnerPurse;
use common\models\PartnerPurseHistory;
use common\models\PartnerSchemes;
use common\models\Payments;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use Yii;
use yii\web\ServerErrorHttpException;

class PartnerPercentCounting
{

    protected
        $exchangeCurrency = [];

    public function countPercentByMonth($beginTime = NULL)
    {
        $time = null === $beginTime ? time() : (is_numeric($beginTime) ? $beginTime : strtotime($beginTime));
        $dateMonthAgo = CustomHelper::getDateMinusNumMonth($time, 1);   //1 month ago (timestamp)
        $beginMonth = CustomHelper::getBeginMonthTime($dateMonthAgo);   //Month end time (timestamp)
        $endMonth = CustomHelper::getEndMonthTime($dateMonthAgo);       //Month begin time (timestamp)
        $arPartners = $this->getPartners();                             //Get partners  (array of objects)
        if(empty($arPartners))
            return TRUE;
        $arSchemes = $this->getSchemes($arPartners);                     //Get all schemes for current partner

        foreach ($arPartners as $partner)
        {
            if(!isset($arSchemes[$partner->partner_scheme]))
                continue;
            $obScheme = $arSchemes[$partner->partner_scheme];           //Scheme for partner

            $arLeads = $this->getPartnerLeads($partner->id);            //Get lead for current partner
            if(count($arLeads) === 0)
                continue;

            $arPayments = $this->getLeadPayments($arLeads,$beginMonth,$endMonth);       //Get lead payments
            if(count($arPayments) === 0)
                continue;

            $arPayments = $this->normalizePayments($arPayments);                        //Convert amount to BYR

            $arPartnerServGroups = $this->getPartnerServiceGroups($obScheme);           //Get partner service group for each service
            $fullAmountByGroup = $this->getPaymentAmount($arPayments,$obScheme->currency_id,$arPartnerServGroups);  //Get full amount at scheme currency

            $percent = $this->getPercent($fullAmountByGroup,$obScheme);        //Get scheme parameters for services(percent and legal params)
            if(count($percent) === 0)
                continue;

            $this->countPercent($arLeads,$arPayments,$percent,$obScheme,$partner);  //Counting percents for partners
        }

        return TRUE;
    }

    /**
     * Get partners
     * @return mixed
     */
    protected function getPartners()
    {
        return CUser::find()
            ->select([
                'id',
                'partner_scheme',
                'archive',
                'partner'
            ])
            ->where('archive = 0 OR archive is NULL')
            ->partner()
            ->all();
    }

    /**
     * Get schemes ids for partners
     * @param array $arPartners
     * @return array
     */
    protected function getSchemesIdsByUser(array $arPartners)
    {
        return ArrayHelper::map($arPartners,'id','partner_scheme');
    }

    /**
     * Get partner schemes with services parameters
     * @param array $arPartners
     * @return array
     */
    protected function getSchemes(array $arPartners)
    {
        $arSchemes = array_values($this->getSchemesIdsByUser($arPartners));
        if(empty($arSchemes))
            return [];
        
        $query = PartnerSchemes::find()
            ->alias('ps')
            ->joinWith('partnerSchemesServices p')
            ->where(['ps' => $arSchemes])
            ->all();
        
        if(empty($query))
            return [];
        
        return CustomHelper::getMapObjectByAttribute($query,'id');
    }

    /**
     * @param $iPartnerId
     * @return mixed
     */
    protected function getPartnerLeads($iPartnerId)
    {
        return PartnerCuserServ::find()
            ->select(['service_id','cuser_id','connect'])
            ->where(['partner_id' => $iPartnerId])
            ->all();
    }

    /**
     * Get all payments for partner lead
     * @param array $arLeads
     * @param $beginMonth
     * @param $endMonth
     * @return array
     */
    protected function getLeadPayments(array $arLeads,$beginMonth,$endMonth)
    {
        if(count($arLeads) === 0)
            return [];

        $arLeads = array_chunk($arLeads,10);
        $arResult = [];
        foreach ($arLeads as $leads) {
            $queryMain = NULL;
            foreach ($leads as $lead) {
                $query = (new Query())
                    ->from(Payments::tableName())
                    ->select(['id', 'pay_date', 'pay_summ', 'service_id','currency_id','legal_id'])
                    ->where(['cuser_id' => $lead->cuser_id, 'service_id' => $lead->service_id])
                    ->andWhere('pay_date >= :beginMonth AND pay_date <=:endMonth AND pay_date >= :connect')
                    ->params([
                        ':beginMonth' => $beginMonth,
                        ':endMonth' => $endMonth,
                        ':connect' => strtotime($lead->connect . ' 00:00:00')
                    ]);
                if (null == $queryMain)
                    $queryMain = $query;
                else
                    $queryMain->union($query);
            }

            $arResultTmp = $queryMain->all();
            $arResult = ArrayHelper::merge($arResult, $arResultTmp);
        }
        return $arResult;
    }

    /**
     * @param $arPayments
     * @return mixed
     */
    protected function normalizePayments($arPayments)
    {
        foreach ($arPayments as &$payment)
        {
            $date = date('Y-m-d',$payment->pay_date);
            $currency = $payment->currency_id;
            if(isset($this->exchangeCurrency[$currency]) && isset($this->exchangeCurrency[$currency][$date]))
            {
                $exchRate = $this->exchangeCurrency[$currency][$date];
            }else{
                $exchRate = ExchangeCurrencyHistory::getCurrencyForDate($date,$currency);
                $this->exchangeCurrency[$currency][$date] = $exchRate;
            }

            $payment->pay_summ = $payment->pay_summ*$exchRate;
        }

        return $arPayments;
    }


    /**
     * @param $arPayments
     * @param $currency
     * @return float|int
     */
    protected function getPaymentAmount($arPayments,$currency,$arPartnerServGroups)
    {
        $amount = [];

        foreach ($arPayments as $payment)
        {
            if(!isset($arPartnerServGroups[$payment->service_id]) || empty($arPartnerServGroups[$payment->service_id]))
                continue;

            $group = $arPartnerServGroups[$payment->service_id];

            $date = date('Y-m-d',$payment->pay_date);
            if(isset($this->exchangeCurrency[$currency]) && isset($this->exchangeCurrency[$currency][$date]))
            {
                $exchRate = $this->exchangeCurrency[$currency][$date];
            }else{
                $exchRate = ExchangeCurrencyHistory::getCurrencyForDate($date,$currency);
                $this->exchangeCurrency[$currency][$date] = $exchRate;
            }

            if(isset($amount[$group]))
                $amount[$group]+= $payment->pay_summ/$exchRate;
            else
                $amount[$group] = $payment->pay_summ/$exchRate;
        }

        return $amount;
    }

    /**
     * @param $fullAmount
     * @param $obScheme
     * @return array
     */
    protected function getPercent($fullAmountByGroup,$obScheme)
    {
        $arServices = $obScheme->partnerSchemesServices;
        if(empty($arServices))
            return [];

        $arResult = [];
        foreach ($arServices as $serv)
        {
            if(!isset($fullAmountByGroup[$serv->group_id]))
                continue;

            $fullAmount = $fullAmountByGroup[$serv->group_id];

            $percent = NULL;
            if(is_array($serv->ranges))
            {
                foreach ($serv->ranges as $range)
                {
                    if($range['left'] >= $fullAmount && $fullAmount <= $range['right'])
                        $percent = $range['percent'];
                }
            }

            if(null !== $percent && $percent > 0)
            {
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
    protected function countPercent($arLeads,$arPayments,$percent,$obScheme,$partner)
    {
        $cuserIDs =  array_unique(ArrayHelper::getColumn($arLeads,'cuser_id'));
        $arCuserResident = ArrayHelper::map(Cuser::find()->select(['id','is_resident'])->where(['id' => $cuserIDs])->all(),'id','is_resident');
        $obPurse = PartnerPurse::find()->where(['cuser_id' => $partner->id])->one();
        if(empty($obPurse))
            $obPurse = new PartnerPurse(['cuser_id' => $partner->id]);

        $rows = [];
        $fullAmount = 0;
        foreach ($arPayments as $payment)
        {
            if(!isset($percent[$payment->service_id]) || !isset($arCuserResident[$payment->cuser_id]))
                continue;

            $amount = $this->getAmount($percent[$payment->service_id],$payment->pay_summ,$payment->legal_id,$arCuserResident[$payment->cuser_id]);

            $rows [] = [
                '',
                $partner->id,
                $amount,
                PartnerPurseHistory::TYPE_INCOMING,
                $payment->id,
                '',
                time(),
                time(),
                $percent[$payment->service_id]
            ];

            $fullAmount+=$amount;
        }
        $transaction = \Yii::$app->db->beginTransaction();
        $historyModel = new PartnerPurseHistory();    //пишем историю
        if(count($rows) > 0)
            if(!Yii::$app->db->createCommand()
                ->batchInsert(PartnerPurseHistory::tableName(), $historyModel->attributes(), $rows)
                ->execute())
            {
                $transaction->rollBack();
                throw new ServerErrorHttpException();
            }

        if(empty($obPurse->amount))
            $obPurse->amount = $fullAmount;
        else
            $obPurse->amount+=$fullAmount;

        if(!$obPurse->save())
        {
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
    protected function getAmount($params,$amount,$legalID,$isResident)
    {
        if(is_array($params['legal']) &&  //проверяем не указано ли для Юр. лица отнимать НАЛОГ от платежа
            isset($params['legal'][$legalID]) &&
            isset($params['legal'][$legalID]['deduct']) &&
            isset($params['legal'][$legalID]['deduct']) == 1) {
            $key = $isResident ? 'res' : 'not_res';
            if (isset($params['legal'][$legalID][$key])) {
                $tax = NULL;
                if (isset($params['legal'][$legalID][$key . '_tax']) && is_numeric($params['legal'][$legalID][$key . '_tax']))
                    $tax = $params['legal'][$legalID][$key . '_tax'];

                $amount = CustomHelper::getVatMountByAmount($amount, $tax); //отнимем от суммы платежа налог
            }
        }
        return $amount*($params['percent']/100);
    }

    /**
     * @param $obScheme
     * @return array
     */
    protected function getPartnerServiceGroups($obScheme)
    {
        $arServices = $obScheme->partnerSchemesServices;
        if(empty($arServices))
            return [];

        return ArrayHelper::map($arServices,'service_id','group_id');
    }

}