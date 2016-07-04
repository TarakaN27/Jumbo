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
use common\models\BonusSchemeToBuser;
use common\models\ExchangeCurrencyHistory;
use common\models\Payments;
use common\models\PaymentsSale;
use yii\helpers\ArrayHelper;

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
        $time = NULL;                   //рассчетное время месяца


    public function __construct($date = NULL)
    {
        $this->time = is_null($date) ? strtotime($date) : time();
    }


    public function run()
    {
        $this->beginMonthTime = CustomHelper::getBeginMonthTime($this->time);
        $this->endMonthTime = CustomHelper::getEndMonthTime($this->time);
        $this->getBUsers();         //get Back user id and his bonus scheme
        $this->getPayments();
        $this->getSales();












    }

    /**
     * @return mixed
     */
    protected function getSales()
    {
        $arPaymentId = [];
        foreach ($this->arPayments as $item)
        {
            $arPaymentId = ArrayHelper::merge($arPaymentId,array_keys($item));
        }
        if(!empty($arPaymentId))
            return $this->arSales = PaymentsSale::find()
                ->select(['payment_id'])
                ->where(['payment_id' => $arPaymentId])
                ->column();

        return [];
    }

    /**
     * @return array
     */
    protected function getPayments()
    {
        $arTmp = Payments::find()
            ->select([
                'p.id',
                'p.pay_date',
                'p.pay_sum',
                'p.prequest_id',
                'p.currency_id',
                'pr.id  as prid',
                'pr.manager_id'
            ])
            ->alias('p')
            ->joinWith('payRequest as pr')
            ->where(['manager_id' => array_keys($this->arBUsers)])
            ->andWhere(['BETWEEN','sale_date',$this->beginMonthTime,$this->endMonthTime])
            ->all();
        return $this->arPayments = ArrayHelper::index($arTmp,'id','payRequest.manager_id');
    }

    /**
     * @return array
     */
    protected function getBUsers()
    {
        $arTmp = BonusSchemeToBuser::find()
            ->alias('bsb')
            ->joinWith('scheme as sc')
            ->where(['bsb.type' => BonusScheme::TYPE_PAYMENT_RECORDS])
            ->all();
        return $this->arBUsers = ArrayHelper::map($arTmp,'buser_id','scheme_id');
    }

    /**
     * @return array
     */
    protected function getAmount()
    {
        $arResult = [];
        foreach ($this->arPayments as $iUserId => $payment)
        {
            foreach ($payment as $iPayId => $pay)
            {
                $tmpAmount = $this->getPaymentAmount($pay);
                if(isset($arResult[$iUserId]))
                {
                    $arResult[$iUserId]+=$tmpAmount;
                }else{
                    $arResult[$iUserId]=$tmpAmount;
                }
            }
        }
        return $this->arAmounts = $arResult;
    }

    /**
     * @param Payments $pay
     * @return float
     */
    protected function getPaymentAmount(Payments $pay)
    {
        $date = date('Y-m-d',$pay->pay_date);
        if(isset($this->arCurrency[$pay->currency_id][$date]))
        {
            $nCurr = $this->arCurrency[$pay->currency_id][$date];
        }else{
            $nCurr = (float)ExchangeCurrencyHistory::getCurrencyInBURForDate($date,$pay->currency_id);
            $this->arCurrency[$pay->currency_id][$date] = $nCurr;
        }

        $amount = (float)$pay->pay_summ*$nCurr;
        return $amount;
    }
}