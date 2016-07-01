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
use common\models\Payments;
use common\models\PaymentsSale;
use yii\helpers\ArrayHelper;

class BonusRecordCalculate
{
    protected
        $arSales = [],
        $arPayments = [],
        $beginMonthTime = NULL,
        $endMonthTime = NULL,
        $arBUsers = [],
        $arAmounts = [],
        $time = NULL;


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

}