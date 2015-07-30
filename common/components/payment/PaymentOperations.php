<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 30.07.15
 * Класс для рассчета платежей
 * Налог / производственные затраты / Прибыль
 */
namespace common\components\payment;

use yii\base\InvalidParamException;

class PaymentOperations {

    public
        $tax,           // налог указывается в процентах 0-100
        $paySumm,       // сумма платежа
        $commission,    // коммиссия
        $corrFactor,    // коректирующий коэфициент
        $sale;          // скидка

    CONST
        ROUND_SIGN = 6; // до скольки знаков округлять
    /**
     * @param $paySumm
     * @param $tax
     * @param $commission
     * @param $corrFactor
     * @param $sale
     */
    public function __construct($paySumm,$tax,$commission,$corrFactor,$sale)
    {
        $this->paySumm = $paySumm;
        $this->tax = $tax;
        $this->commission = $commission;
        $this->corrFactor = $corrFactor;
        $this->sale = $sale;
        $this->validate();
    }

    /**
     * @throws \yii\base\InvalidParamException
     */
    protected function validate()
    {
        if($this->sale > 100 || $this->sale < 0)
            throw new InvalidParamException("Sale must be more than 0 and less then 100 (0 <= sale <= 100)");

        if($this->commission > 100 || $this->commission < 0)
            throw new InvalidParamException("Commission must be more than 0 and less then 100 (0 <= commission <= 100)");

        if($this->tax > 100 || $this->tax < 0)
            throw new InvalidParamException("Tax must be more than 0 and less then 100 (0 <= tax <= 100)");

        if($this->corrFactor < 0)
            throw new InvalidParamException("Correction factor must be more than 0  (0 <= correction factor )");

        if($this->paySumm < 0)
            throw new InvalidParamException("Payment summ factor must be more than 0  (0 <= payment summ )");
    }

    /**
     * Налог
     * @return float
     */
    public function getTaxValue()
    {
        return round($this->paySumm*$this->tax/100,self::ROUND_SIGN);
    }

    /**
     * Производственные затраты
     * @return mixed
     */
    public function getProductionCostsValue()
    {
        return round($this->paySumm * (1 - $this->commission/100) * $this->corrFactor * (1 - $this->sale/100),self::ROUND_SIGN);
    }

    /**
     * Прибыль
     * @return float
     */
    public function getProfitValue()
    {
        return round($this->paySumm - $this->getTaxValue() - $this->getProductionCostsValue(),self::ROUND_SIGN);
    }

    /**
     * Получение всех рассчетов
     * @return array
     */
    public function getFullCalculate()
    {
        return [
            'tax' => $this->getTaxValue(),
            'production' => $this->getProductionCostsValue(),
            'profit' => $this->getProfitValue()
        ];
    }

} 