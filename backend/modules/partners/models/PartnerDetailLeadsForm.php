<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 21.4.16
 * Time: 12.56
 */

namespace backend\modules\partners\models;


use common\models\CUser;
use common\models\ExchangeCurrencyHistory;
use common\models\PartnerCuserServ;
use common\models\PartnerPurseHistory;
use common\models\Services;
use yii\base\Model;
use Yii;
use yii\helpers\ArrayHelper;

class PartnerDetailLeadsForm extends Model
{
    public 
        $beginDate = NULL,
        $endDate = NULL,
        $obPartner = NULL;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['obPartner','safe'],
            [['beginDate','endDate'],'required'],
            [['beginDate','endDate'],'date','format' => 'php:d.m.Y'],
            [['beginDate','endDate'],'validateDate']
        ];
    }

    /**
     * @param $attribute
     * @param $params
     */
    public function validateDate($attribute,$params)
    {
        if(strtotime($this->beginDate) > strtotime($this->endDate))
            $this->addError($attribute,\Yii::t('app/users','Begin date must be less than end date'));
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'beginDate' => Yii::t('app/users','Begin date'),
            'endDate' => Yii::t('app/users','End date')
        ];
    }


    public function makeRequest()
    {
        $beginTime = strtotime($this->beginDate.' 00:00:00');
        $endTime = strtotime($this->endDate.' 00:00:00');
        $arPrev = $this->getPervPeriodStat($beginTime);   //получаем сумму
        $arLeads =  $this->getLeads();
        $arCurrPeriod = $this->getCurrentPeriodStat($beginTime,$endTime);

        $arStatIncomingByLead = $this->getStatIncomingByLeads($arCurrPeriod);
        $arStatWithdrawal = $this->getStatWithdrawal($arCurrPeriod);
        $arStatFull = $this->getStatFullSortByDate($arCurrPeriod);
        $arStatIncomingByLead = $this->normalizeStatIncomingByLead($arStatIncomingByLead);
        $arLeadsDetail = $this->getLeadsDetail($arLeads);
        //$arServIds = array_unique(ArrayHelper::getColumn($arCurrPeriod,'service_id'));
        //$arService = ArrayHelper::map(Services::find()->select(['id','name'])->where(['id' => $arServIds])->all(),'id','name');
        $arService = array_unique(ArrayHelper::map($arCurrPeriod,'payment.service_id','payment.service.name'));
        return [
            'prev' => $arPrev,
            'incoming' => $arStatIncomingByLead,
            'fullStat' => $arStatFull,
            'withdrawal' => $arStatWithdrawal,
            'arLeads' => $arLeadsDetail,
            'arService' => $arService
        ];
    }


    /**
     * @return array
     */
    protected function getPervPeriodStat($beginDate)
    {
        $sumIncoming = PartnerPurseHistory::find()
            ->select(['h.id','p.id as pid','p.pay_date','h.type','h.amount','h,payment_id'])
            ->alias('h')
            ->joinWith('payment p')
            ->where('p.pay_date < :beginDate')
            ->andWhere([
                'h.type' => PartnerPurseHistory::TYPE_INCOMING,
                'h.cuser_id' => $this->obPartner->id
            ])
            ->params([':beginDate' => $beginDate])
            ->sum('h.amount');

        $sumExpense = PartnerPurseHistory::find()
            ->select(['h.id','p.id as pid','p.pay_date','h.type','h.amount','h,payment_id'])
            ->alias('h')
            ->joinWith('payment p')
            ->where('p.pay_date < :beginDate')
            ->andWhere([
                'h.type' => PartnerPurseHistory::TYPE_EXPENSE,
                'h.cuser_id' => $this->obPartner->id
            ])
            ->params([':beginDate' => $beginDate])
            ->sum('h.amount');

        return [
            'incoming' => (float)$sumIncoming,
            'expense' => (float)$sumExpense
        ];
    }

    /**
     * @param $beginDate
     * @param $endDate
     * @return mixed
     */
    protected function getCurrentPeriodStat($beginDate,$endDate)
    {
        return PartnerPurseHistory::find()->with('payment.currency')
            ->select([
                'h.*',
                'p.id as pid',
                'p.pay_summ',
                'p.cuser_id as pcuser_id',
                'p.currency_id',
                'p.pay_date',
                'p.service_id',
                's.name as serv_name'
            ])
            ->alias('h')
            ->joinWith('payment p')
            ->joinWith('payment.service s')
            ->where('p.pay_date >= :beginDate AND p.pay_date <= :endDate ')
            ->andWhere(['h.cuser_id' => $this->obPartner->id])
            ->params([':beginDate' => $beginDate,':endDate' => $endDate])
            ->all();
    }

    /**
     * @return mixed
     */
    protected function getLeads()
    {
        return PartnerCuserServ::find()->where(['partner_id' => $this->obPartner->id])->all();
    }

    /**
     * @param $arCurrPeriod
     * @return array
     */
    protected function getStatIncomingByLeads($arCurrPeriod)
    {
        if(empty($arCurrPeriod))
            return [];

        $arResult = [];
        foreach ($arCurrPeriod as $stat)
        {
            if($stat->type != PartnerPurseHistory::TYPE_INCOMING)
                continue;

            $obPayment = $stat->payment;
            if(!$obPayment)
                continue;
            $leadID = $obPayment->cuser_id;
            $arResult[$leadID][$obPayment->service_id][] = $stat;
        }

        return $arResult;
    }

    /**
     * @param $arCurrPeriod
     * @return array
     */
    protected function getStatWithdrawal($arCurrPeriod){
        if(empty($arCurrPeriod))
            return [];

        $arResult = [];
        foreach ($arCurrPeriod as $stat)
        {
            if($stat->type != PartnerPurseHistory::TYPE_EXPENSE)
                continue;

            $arResult []= $stat;
        }

        return $arResult;
    }

    /**
     * @param $arCurrPeriod
     * @return array
     */
    protected function getStatFullSortByDate($arCurrPeriod)
    {
        if(empty($arCurrPeriod))
            return [];

        usort($arCurrPeriod,function($a, $b){
            $aTime = $a->type == PartnerPurseHistory::TYPE_EXPENSE  ? $a->created_at : $a->payment->pay_date;
            $bTime = $b->type == PartnerPurseHistory::TYPE_EXPENSE  ? $b->created_at : $b->payment->pay_date;

            return ($aTime < $bTime) ? -1 : 1;
        });
        return $arCurrPeriod;
    }

    /**
     * @param $arStatIncomingByLead
     * @return array
     */
    protected function normalizeStatIncomingByLead($arStatIncomingByLead)
    {
        $arResult = [];
        $arExchRates =[];
        foreach ($arStatIncomingByLead as $leadID => $item)
        {
            $arLeadDetail = [
                'amount' => 0,
                'payAmount' => 0
            ];
            foreach ($item as $servID => $stat)
            {
               $arServicesStat = [
                   'amount' => 0,
                   'payAmount' => 0
               ] ;

                foreach($stat as $value)
                {
                    $arServicesStat['amount']+=$value->amount;
                    $arLeadDetail['amount']+=$value->amount;

                    $payDate = date('Y-m-d',$value->payment->pay_date);
                    $payCurr = $value->payment->currency_id;
                    if(isset($arExchRates[$payCurr][$payDate]))
                        $exRate = $arExchRates[$payCurr][$payDate];
                    else
                    {
                        $exRate = ExchangeCurrencyHistory::getCurrencyInBURForDate($value->payment->pay_date,$value->payment->currency_id);
                        $arExchRates[$payCurr][$payDate]  = $exRate;
                    }
                    $arServicesStat['payAmount']+=$value->payment->pay_summ*$exRate;
                    $arLeadDetail['payAmount']+=$value->payment->pay_summ*$exRate;
                }

                if(!isset($arResult[$leadID]['services']))
                    $arResult[$leadID]['services'] = [
                        $servID => [
                            'stat' => $stat,
                            'detail' => $arServicesStat
                         ]
                    ];
                else
                    $arResult[$leadID]['services'][$servID] = ['stat' => $stat, 'detail' => $arServicesStat];
            }
            $arResult[$leadID]['stat'] = $arLeadDetail;
        }

        return $arResult;
    }

    /**
     * @param $arLeads
     * @return array
     */
    public function getLeadsDetail($arLeads)
    {
        if(empty($arLeads))
            return [];
        
        return CUser::getInfoByIds(ArrayHelper::getColumn($arLeads,'cuser_id'));
    }

}