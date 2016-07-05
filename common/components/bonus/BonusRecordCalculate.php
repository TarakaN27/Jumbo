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
use common\models\BonusSchemeRecords;
use common\models\BonusSchemeRecordsHistory;
use common\models\BonusSchemeToBuser;
use common\models\BUserBonus;
use common\models\BUserPaymentRecords;
use common\models\ExchangeCurrencyHistory;
use common\models\Payments;
use common\models\PaymentsSale;
use yii\base\Exception;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\web\ServerErrorHttpException;

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
        $this->getBUsers();                                     //get Back user id and his bonus scheme
        if (count($this->arBUsers) == 0)
            return FALSE;

        $this->getPayments();                                   //get paymetns for user and current period
        if (count($this->arPayments) == 0)
            return FALSE;

        $this->getSales();                                      //get payment is witch was sales
        $this->getAmount();                                     //get payment amount for each buser
        if (count($this->arAmounts) == 0)
            return FALSE;

        $this->getSchemesParams();                              //get parameters for bonus scheme
        if (count($this->arSchemesParams) == 0)
            return FALSE;

        $this->getPrevRecordsForUser();                         //get prev records for users
        $tr = \Yii::$app->db->beginTransaction();
        try {
            $this->getNewRecordsForBUserRecordPayment();        //
            //$this->insertRecords();                             //insert to records table
            $tr->commit();
        } catch (Exception $e) {
            $tr->rollBack();
        }

        return TRUE;
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
    protected function getPayments()
    {
        $arTmp = Payments::find()
            ->select([
                'p.id',
                'p.pay_date',
                'p.pay_summ',
                'p.prequest_id',
                'p.currency_id',
                'pr.id  as prid',
                'pr.manager_id'
            ])
            ->alias('p')
            ->joinWith('payRequest as pr')
            ->where(['manager_id' => array_keys($this->arBUsers)])
            ->andWhere(['BETWEEN', 'p.pay_date', $this->beginMonthTime, $this->endMonthTime])
            ->all();
        return $this->arPayments = ArrayHelper::index($arTmp, 'id', 'payRequest.manager_id');
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
            ->where(['sc.type' => BonusScheme::TYPE_PAYMENT_RECORDS])
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
            foreach ($payment as $iPayId => $pay) {
                if(in_array($iPayId,$this->arSales))
                    continue;
                $tmpAmount = $this->getPaymentAmount($pay);
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
    protected function getPaymentAmount(Payments $pay)
    {
        $date = date('Y-m-d', $pay->pay_date);
        if (isset($this->arCurrency[$pay->currency_id][$date])) {
            $nCurr = $this->arCurrency[$pay->currency_id][$date];
        } else {
            $nCurr = (float)ExchangeCurrencyHistory::getCurrencyInBURForDate($date, $pay->currency_id);
            $this->arCurrency[$pay->currency_id][$date] = $nCurr;
        }

        $amount = (float)$pay->pay_summ * $nCurr;
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
     * @return bool|int
     * @throws \yii\db\Exception
     */
    protected function insertRecords()
    {
        if(count($this->arNewRecordForInsert) == 0)
            return FALSE;

        $model = new BUserPaymentRecords();
        return \Yii::$app->db->createCommand()
            ->batchInsert($model::tableName(), $model->attributes(), $this->arNewRecordForInsert)
            ->execute();
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

    protected function getPercent($oldAmount,$newAmount)
    {
        return ((float)$newAmount/(float)$oldAmount)*100 - 100;
    }
}