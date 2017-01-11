<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 1.7.16
 * Time: 16.37
 */

namespace common\components\bonus;

use common\components\helpers\CustomHelper;
use common\models\BonusScheme;
use common\models\BonusSchemeRecordsHistory;
use common\models\BonusSchemeToBuser;
use common\models\BUserBonus;
use common\models\BUserBonusMonthCoeff;
use common\models\BUserPaymentRecords;
use common\models\ExchangeCurrencyHistory;
use common\models\Payments;
use common\models\PaymentsSale;
use yii\base\Exception;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\web\ServerErrorHttpException;
use Yii;

class BonusRecordCalculate
{
    protected
        $arSales = [],                  //продажи
        $arPayments = [],               //платежи
        $beginMonthTime = NULL,         //время начала рассчетного месяца
        $endMonthTime = NULL,           //время окончания рассчетного месяца
        $arBUsers = [],                 //пользователи со схемами для которых рассчитываем бонус
        $arAmounts = [],                //суммы плтажей по пользователям за рассчетный месяц, без учета продаж
        $arCurrency = [],               //курсы валют
        $arPrevRecords = [],            //предыдущие рекорды пользователй
        $arNewRecordForInsert = [],     //записи для вставки в таблицу рекордов
        $arSchemesParams = [],          //парметры схем
        $arUserSchemes = [],
        $arSchemesRecord = [],
        $arBUserBonus,                  //массив с бонусом для пользователя
        $time = NULL;                   //рассчетное время месяца

    public function __construct($date = NULL)
    {
        $this->time = !is_null($date) ? strtotime($date) : time();
    }

    /**
     * @return bool
     */
    public function run()
    {
        $this->beginMonthTime = CustomHelper::getBeginMonthTime($this->time);
        $this->endMonthTime = CustomHelper::getEndMonthTime($this->time);
        $users = $this->getBUsers();
        if (count($users) == 0)
            return FALSE;
        $this->setShemesRecord($users);

        foreach($users as $bUser) {
            //если продажник то посчитаем ему только продажи прошлого месяца
            if($bUser->scheme->payment_base == BonusScheme::BASE_ALL_PAYMENT_SALED_CLENT){
                $sum = $this->getTotalSumProfit($bUser->buser_id, $this->beginMonthTime, $this->endMonthTime, true);
            }elseif($bUser->scheme->payment_base == BonusScheme::BASE_OWN_PAYMENT){
                $sumCurrent = $this->getTotalSumProfit($bUser->buser_id, $this->beginMonthTime, $this->endMonthTime);
                $sumPrevMonth = $this->getTotalSumProfitPrevMonth($bUser->buser_id,$this->beginMonthTime);
                $sum = $sumPrevMonth - $sumCurrent;
            }
            if($coeff = $this->getMonthCoeff($sum, $bUser->buser_id)) {
                //у продажников уменьшающий коэфф действует на текущий месяц, а увеличивающий на следующий
                if($coeff<1 && $bUser->scheme->payment_base == BonusScheme::BASE_ALL_PAYMENT_SALED_CLENT){
                    $coeff = str_replace(",",'.',$coeff);
                    Yii::$app->db->createCommand("UPDATE ".BUserBonus::tableName()." b INNER JOIN ".Payments::tableName()." p ON b.payment_id=p.id SET b.amount=b.amount*$coeff, b.bonus_percent=b.bonus_percent*$coeff WHERE b.buser_id=$bUser->buser_id and p.pay_date BETWEEN $this->beginMonthTime AND $this->endMonthTime")->query();
                    $year = date("Y", $this->endMonthTime-10);
                    $month = date("m", $this->endMonthTime -10);
                    $this->updateMonthCoeff($coeff, $bUser->buser_id, $year, $month);
                    $coeff=1;
                }
                $this->saveMonthCoeff($coeff,$bUser->buser_id);
            }
        }
        return TRUE;
    }
    public function getMonthCoeff($sum, $userId)
    {
        $schemeRecord = $this->arSchemesRecord[$this->arUserSchemes[$userId]];
        $koeff = 1;
        foreach ($schemeRecord as $key => $record) {
            if ($key != 'exclude_sale') {
                if ($sum >= $record['from'] && $sum < $record['to']) {
                    $koeff = $record['rate'];
                    break;
                }
            }
        }
        return $koeff;
    }

    public function saveMonthCoeff($koeff, $userId){

            $year = date("Y", $this->endMonthTime+10);
            $month = date("m", $this->endMonthTime + 10);
            BUserBonusMonthCoeff::deleteAll(['buser_id' => $userId, 'month' => $month, 'year' => $year]);
            $buserBonusMonthCoeff = new BUserBonusMonthCoeff();
            $buserBonusMonthCoeff->buser_id = $userId;
            $buserBonusMonthCoeff->month = $month;
            $buserBonusMonthCoeff->year = $year;
            $buserBonusMonthCoeff->coeff = str_replace(",", ".",$koeff);
            $buserBonusMonthCoeff->save();
    }

    public function updateMonthCoeff($koeff, $userId, $year, $month){
        BUserBonusMonthCoeff::deleteAll(['buser_id' => $userId, 'month' => $month, 'year' => $year]);
        $buserBonusMonthCoeff = new BUserBonusMonthCoeff();
        $buserBonusMonthCoeff->buser_id = $userId;
        $buserBonusMonthCoeff->month = $month;
        $buserBonusMonthCoeff->year = $year;
        $buserBonusMonthCoeff->coeff = str_replace(",", ".",$koeff);
        $buserBonusMonthCoeff->save();
    }

    public function setShemesRecord($users){
        foreach($users as $user){
            if(!isset($this->arSchemesRecord[$user->scheme->id])){
                $this->arSchemesRecord[$user->scheme->id] = $user->scheme->schemeRecords->params;
            }
            $this->arUserSchemes[$user->buser_id] = $user->scheme->id;
        }
    }

    /**
     * Получаем id платежей, которые являются продажами
     * @return mixed
     */
    protected function getSales()
    {
        $arPaymentId = [];
        foreach ($this->arPayments as $item) {
            $arPaymentId = ArrayHelper::merge($arPaymentId, array_keys($item));
        }
        if (!empty($arPaymentId))
            return $this->arSales = PaymentsSale::find()
                ->select(['payment_id'])
                ->where(['payment_id' => $arPaymentId])
                ->column();

        return [];
    }

    /**
     * Получаем платежи
     * @return array
     */
    public function getTotalSumProfit($userId, $start, $end, $onlySale = false)
    {
        $schemeRecord = $this->arSchemesRecord[$this->arUserSchemes[$userId]];
        $sum = BUserBonus::find()
            ->select(['totalSum'=>'SUM(profit_for_manager)', 'b.payment_id'])
            ->alias('b')
            ->joinWith('calculation as c')
            ->joinWith('payment as p')
            ->where(['b.buser_id' => $userId])
            ->andWhere(['BETWEEN', 'p.pay_date', $start, $end]);
        if($schemeRecord['exclude_sale'] == 1){
            $sum->andWhere(['<>','b.number_month',1]);
        }
        if($onlySale){
            $sum->andWhere(['b.is_sale'=>1]);
        }
        $sum = $sum->asArray()->one();
        return (float)$sum["totalSum"];
    }

    public function getTotalSumProfitPrevMonth($userId, $start)
    {
        $beginMonthTime = CustomHelper::getBeginMonthTime($start-1);
        $endMonthTime = CustomHelper::getEndMonthTime($beginMonthTime);
        $sum = BUserBonus::find()
            ->select(['totalSum'=>'SUM(profit_for_manager)', 'b.payment_id'])
            ->alias('b')
            ->joinWith('calculation as c')
            ->joinWith('payment as p')
            ->where(['b.buser_id' => $userId])
            ->andWhere(['BETWEEN', 'p.pay_date', $beginMonthTime, $endMonthTime]);
        $sum = $sum->asArray()->one();
        return (float)$sum["totalSum"];
    }

    /**
     * Получаем пользователей и ID схемы к которой привязан пользователь
     * @return array
     */
    protected function getBUsers()
    {
        return $this->arBUsers = BonusSchemeToBuser::find()
            ->alias('bsb')
            ->joinWith('scheme as sc')
            ->where(['sc.type' => BonusScheme::TYPE_PROFIT_PAYMENT])
            ->indexBy('buser_id')
            ->all();
    }

    /**
     * Посчитаем платежи для каждого пользователя за указанный период без учета продаж
     * @return array
     */
    protected function getAmount()
    {
        $arResult = [];
        foreach ($this->arPayments as $iUserId => $payment) {
            $iSchemeID = $this->arBUsers[$iUserId]->scheme_id;
            foreach ($payment as $iPayId => $pay) {
                if(in_array($iPayId,$this->arSales))
                    continue;
                $tmpAmount = $this->getPaymentAmount($pay,$iSchemeID);
                if (isset($arResult[$iUserId])) {
                    $arResult[$iUserId] += $tmpAmount;
                } else {
                    $arResult[$iUserId] = $tmpAmount;
                }
            }
        }
        return $this->arAmounts = $arResult;
    }

    /**
     * Приведем платежи к единой валюте (BYR)
     * @param Payments $pay
     * @return float
     */
    protected function getPaymentAmount(Payments $pay,$iSchemeID)
    {
        $date = date('Y-m-d', $pay->pay_date);
        if (isset($this->arCurrency[$pay->currency_id][$date])) {
            $nCurr = $this->arCurrency[$pay->currency_id][$date];
        } else {
            $nCurr = (float)ExchangeCurrencyHistory::getCurrencyInBURForDate($date, $pay->currency_id);
            $this->arCurrency[$pay->currency_id][$date] = $nCurr;
        }

        $amount = (float)$pay->pay_summ * $nCurr;
        $amount = $this->getPaymentAmountWithoutTax($amount,$pay,$iSchemeID);

        return $amount;
    }

    /**
     * Вычтем из платежей НДС если нужно
     * @param $amount
     * @param Payments $payment
     * @param $iSchemeID
     * @return float
     */
    protected function getPaymentAmountWithoutTax($amount,Payments $payment,$iSchemeID)
    {
        if(!isset($this->arSchemesParams[$iSchemeID]) || empty($this->arSchemesParams[$iSchemeID]->deduct_lp))
            return $amount;

        $arParams = $this->arSchemesParams[$iSchemeID]->deduct_lp;      //парметры схемы по вычету НДС

        if(is_array($arParams) &&  //проверяем не указано ли для Юр. лица отнимать НАЛОГ от платежа
            isset($arParams[$payment->legal_id],$arParams[$payment->legal_id]['deduct']) &&
            $arParams[$payment->legal_id]['deduct'] == 1)
        {
            $bIsResident = ArrayHelper::getValue($payment,'cuser.is_resident');
            if(is_null($bIsResident))
                return $amount;

            $key = $bIsResident ? 'res' : 'not_res';
            if(isset($arParams[$payment->legal_id][$key]))
            {
                $tax = NULL;
                if(isset($arParams[$payment->legal_id][$key.'_tax']) && is_numeric($arParams[$payment->legal_id][$key.'_tax']))
                    $tax = $arParams[$payment->legal_id][$key.'_tax'];
                $amount = CustomHelper::getVatMountByAmount($amount,$tax); //отнимем от суммы платежа налог
            }

        }
        return $amount;
    }

    /**
     * Получим предыдущие рекорд пользователя
     * @return array
     */
    protected function getPrevRecordsForUser()
    {
        return $this->arPrevRecords = (new Query())
            ->select(['buser_id', 'MAX(amount) as amount', 'MAX(record_num) as record_num'])
            ->from(BUserPaymentRecords::tableName())
            ->groupBy(['buser_id'])
            ->where(['buser_id' => array_keys($this->arBUsers)])
            ->andWhere(['<', 'record_date', date('Y-m-d', $this->beginMonthTime)])
            ->indexBy('buser_id')
            ->all();
    }

    /**
     * Получим параметры схемы для начисления бонуса.
     * @return mixed
     */
    protected function getSchemesParams()
    {
        return $this->arSchemesParams = BonusSchemeRecordsHistory::getParamsForFewSchemes(ArrayHelper::getColumn($this->arBUsers,'scheme_id'), $this->time);
    }

    /**
     * Соберем записи для добавления в таблицу рекордов поьзователя
     * @return array
     */
    protected function getNewRecordsForBUserRecordPayment()
    {
        foreach ($this->arBUsers as $iBuserId => $arSchemeLink) {
            $iSchemeId = $arSchemeLink->scheme_id;

            if (!isset($this->arSchemesParams[$iSchemeId]))
                continue;

            if (!isset($this->arAmounts[$iBuserId]))
                continue;

            $amount = (float)$this->arAmounts[$iBuserId];

            $prevRecord = NULL;
            if (isset($this->arPrevRecords[$iBuserId]))
                $prevRecord = $this->arPrevRecords[$iBuserId];

            $bIsNewRecord = 0;
            $recordNum = '';
            $bonusAmount = NULL;
            $prevAmount = $prevRecord ? (float)$prevRecord['amount'] : NULL;
            $percent = '';
            if ($prevRecord && $prevAmount < $amount) {
                $bIsNewRecord = 1;
                $recordNum = empty($prevRecord['record_num']) ? 1 : (int)$prevRecord['record_num'] + 1;
                $percent = $this->getPercent($prevAmount,$amount);
                $bonusAmount = $this->getUserBonusAmount($percent,$iSchemeId);
            }

            $iRecordId = $this->addNewRecordToNewRecordForInsert($iBuserId, $amount, date('Y-m-d', $this->beginMonthTime), $bIsNewRecord, $recordNum,$percent);
            if ($bIsNewRecord && !is_null($bonusAmount)) {
                $this->addBUserBonus($iBuserId, $bonusAmount, ArrayHelper::getValue($arSchemeLink, 'scheme.currency_id'), $iSchemeId,$iRecordId);
            }
        }

        return $this->arNewRecordForInsert;
    }

    /**
     * @param $iBuserId
     * @param $amount
     * @param $recordDate
     * @param int $bIsRecord
     * @param string $iRecordNum
     * @param string $percent
     * @return BUserPaymentRecords
     * @throws ServerErrorHttpException
     */
    protected function addNewRecordToNewRecordForInsert($iBuserId, $amount, $recordDate, $bIsRecord = 0, $iRecordNum = '',$percent = '')
    {
        $obRecord = new BUserPaymentRecords();
        $obRecord->buser_id = $iBuserId;
        $obRecord->amount = $amount;
        $obRecord->record_date = $recordDate;
        $obRecord->is_record = $bIsRecord;
        $obRecord->record_num = $iRecordNum;
        $obRecord->percents = $percent;
        if(!$obRecord->save())
            throw new ServerErrorHttpException;

        return $obRecord->id;
    }

    /**
     * @param $iBUserId
     * @param $amount
     * @param $iCurrencyId
     * @param $iSchemeId
     * @return bool
     */
    protected function addBUserBonus($iBUserId, $amount, $iCurrencyId, $iSchemeId,$iRecordId)
    {
        $obBonus = new BUserBonus([
            'amount' => $amount,
            'buser_id' => $iBUserId,
            'currency_id' => $iCurrencyId,
            'scheme_id' => $iSchemeId,
            'record_id' => $iRecordId
        ]);

        return $obBonus->save();
    }

    /**
     * @param $percent
     * @param $iSchemeId
     * @return float|null
     */
    protected function getUserBonusAmount($percent,$iSchemeId)
    {
        $arParams = $this->arSchemesParams[$iSchemeId];                     //get scheme params
        $amount = NULL;
        foreach ($arParams->params as $item)
        {
            if((float)$item['from'] <= $percent)
            {
                $amount = (float)$item['rate'];
            }
            if((float)$item['to'] >= $percent)
                break;
        }
        return $amount;
    }

    /**
     * @param $oldAmount
     * @param $newAmount
     * @return float
     */
    protected function getPercent($oldAmount,$newAmount)
    {
        return CustomHelper::getDiffTwoNumbersAtPercent($oldAmount,$newAmount);
    }

    public function getCoeffNextMonth($userIds, $now){
        $this->beginMonthTime = CustomHelper::getBeginMonthTime($now);
        $this->endMonthTime = CustomHelper::getEndMonthTime($now);
        $this->arBUsers = BonusSchemeToBuser::find()
            ->alias('bsb')
            ->joinWith('scheme as sc')
            ->where(['sc.type' => BonusScheme::TYPE_PROFIT_PAYMENT, 'buser_id'=>$userIds])
            ->indexBy('buser_id')
            ->all();
        if (count($this->arBUsers) == 0)
            return FALSE;
        $this->setShemesRecord($this->arBUsers);
        $coeffNextMonth = [];
        foreach($this->arBUsers as $bUser) {
            //если продажник то посчитаем ему только продажи прошлого месяца
            if($bUser->scheme->payment_base == BonusScheme::BASE_ALL_PAYMENT_SALED_CLENT){
                $sum = $this->getTotalSumProfit($bUser->buser_id, $this->beginMonthTime, $this->endMonthTime, true);
            }elseif($bUser->scheme->payment_base == BonusScheme::BASE_OWN_PAYMENT){
                $sumCurrent = $this->getTotalSumProfit($bUser->buser_id, $this->beginMonthTime, $this->endMonthTime);
                $sumPrevMonth = $this->getTotalSumProfitPrevMonth($bUser->buser_id,$this->beginMonthTime);
                $sum = $sumPrevMonth - $sumCurrent;
            }
            $coeffNextMonth[$bUser->buser_id] = $this->getMonthCoeff($sum,$bUser->buser_id);
        }
        return $coeffNextMonth;
    }
}