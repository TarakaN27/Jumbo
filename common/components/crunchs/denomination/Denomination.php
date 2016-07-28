<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 6.7.16
 * Time: 14.55
 */

namespace common\components\crunchs\denomination;


use common\models\Acts;
use common\models\ActServices;
use common\models\ActToPayments;
use common\models\Bills;
use common\models\BonusScheme;
use common\models\BonusSchemeService;
use common\models\BonusSchemeServiceHistory;
use common\models\BUserBonus;
use common\models\BUserPaymentRecords;
use common\models\Config;
use common\models\ExchangeCurrencyHistory;
use common\models\ExchangeRates;
use common\models\Expense;
use common\models\PartnerPurse;
use common\models\PartnerPurseHistory;
use common\models\PartnerWithdrawalRequest;
use common\models\PaymentCondition;
use common\models\PaymentRequest;
use common\models\Payments;
use common\models\PaymentsCalculations;
use common\models\ServiceRateHist;
use common\models\Services;
use yii\db\Query;
use yii\web\ServerErrorHttpException;

class Denomination
{
    CONST
        CORR_FACTOR = 10000;

    /**
     * @return \yii\db\Connection
     */
    protected function getConnection()
    {
        return \Yii::$app->getDb();
    }

    /**
     * @param $sql
     * @return int
     * @throws \yii\db\Exception
     */
    protected function sqlExecute($sql)
    {
        $con = $this->getConnection();
        $query = $con->createCommand($sql);
        return $query->execute();
    }

    /**
     * JUMBO-373
     * http://wiki.webmart.by/pages/viewpage.action?pageId=3276803
     */
    public function run()
    {
        #1
        echo 'services</br>'.PHP_EOL;
        $this->services();
        echo ' end services</br>'.PHP_EOL;

        #2
        echo 'exchange rate</br>'.PHP_EOL;
        $this->exchangeRate();
        echo 'end exchange rate</br>'.PHP_EOL;

        #3
        echo 'Payments request</br>'.PHP_EOL;
        $this->paymentRequest();
        echo ' end Payments request</br>'.PHP_EOL;

        #4
        echo 'Payments </br>'.PHP_EOL;
        $this->payment();
        echo ' end Payments</br>'.PHP_EOL;

        #8
        echo 'Expense </br>'.PHP_EOL;
        $this->expense();
        echo ' end expense</br>'.PHP_EOL;

        #9
        echo 'Act </br>'.PHP_EOL;
        $this->act();
        echo ' end act</br>'.PHP_EOL;

        #10
        echo 'Bonus scheme </br>'.PHP_EOL;
        $this->bonusScheme();
        echo ' end bonus scheme</br>'.PHP_EOL;

        #12
        echo 'partner </br>'.PHP_EOL;
        $this->partner();
        echo ' end partner</br>'.PHP_EOL;

        #14
        echo 'bills </br>'.PHP_EOL;
        $this->bills();
        echo ' end bills</br>'.PHP_EOL;

        #16
        echo 'Buser bonus </br>'.PHP_EOL;
        $this->buserBonus();
        echo 'End buser bonus</br>'.PHP_EOL;

        #17
        echo 'Config </br>'.PHP_EOL;
        $this->config();
        echo 'End config</br>'.PHP_EOL;

        #18
        echo 'Buser payments record</br>'.PHP_EOL;
        $this->userPaymentsRecord();
        echo 'End buser payments record</br>'.PHP_EOL;
        die('end');
    }

    /**
     * @return bool
     * @throws ServerErrorHttpException
     */
    protected function services()
    {
        $sql = 'UPDATE '.Services::tableName().' set rate=rate/10000';
        if($this->sqlExecute($sql)===false)
        {
            throw new ServerErrorHttpException('Service query error');
        }

        $sql = 'UPDATE '.ServiceRateHist::tableName().' set old_rate = old_rate/10000, new_rate = new_rate/10000';
        if($this->sqlExecute($sql)===false)
        {
            throw new ServerErrorHttpException('Service hist query error');
        }

        return TRUE;
    }

    protected function exchangeRate()
    {
        if(!ExchangeRates::updateAll(['code' => 'BYN','cbr' => '933'],['id' => ExchangeRates::BYN_ID]))
            throw new ServerErrorHttpException('Exchange rate change code');

        $sql = 'UPDATE '.ExchangeRates::tableName().' set nbrb_rate = nbrb_rate/10000 WHERE id != '.ExchangeRates::BYN_ID;
        if($this->sqlExecute($sql)===false)
        {
            throw new ServerErrorHttpException('Exchange rate');
        }

        $sql = 'UPDATE '.ExchangeCurrencyHistory::tableName().' set rate_nbrb = rate_nbrb/10000,old_rate_nbrb = old_rate_nbrb/10000 WHERE currency_id != '.ExchangeRates::BYN_ID;
        if($this->sqlExecute($sql)===false)
        {
            throw new ServerErrorHttpException('Exchange rate history');
        }
    }

    protected function paymentRequest()
    {
        $sql = 'UPDATE '.PaymentRequest::tableName().' set pay_summ = pay_summ/10000 WHERE currency_id = '.ExchangeRates::BYN_ID;
        if($this->sqlExecute($sql)===false)
        {
            throw new ServerErrorHttpException('Exchange rate');
        }
    }

    protected function payment()
    {
        $sql = 'UPDATE '.Payments::tableName().' set pay_summ = pay_summ/10000 WHERE currency_id = '.ExchangeRates::BYN_ID;
        if($this->sqlExecute($sql)===false)
        {
            throw new ServerErrorHttpException('Payment');
        }

        $sql = 'UPDATE '.PaymentsCalculations::tableName().' SET tax = tax/10000, profit =  profit/10000, production = production/10000';
        if($this->sqlExecute($sql)===false)
        {
            throw new ServerErrorHttpException('Payment calculations');
        }

        $sql = 'UPDATE '.PaymentCondition::tableName().' SET summ_from = summ_from/10000, summ_to = summ_to/10000 WHERE currency_id = '.ExchangeRates::BYN_ID;
        if($this->sqlExecute($sql)===false)
        {
            throw new ServerErrorHttpException('Payment conditions');
        }
    }

    protected function expense()
    {
        $sql = 'UPDATE '.Expense::tableName().' set pay_summ = pay_summ/10000 WHERE currency_id = '.ExchangeRates::BYN_ID;
        if($this->sqlExecute($sql)===false)
        {
            throw new ServerErrorHttpException('Expense');
        }
    }

    protected function act()
    {
        $sql = 'UPDATE '.Acts::tableName().' set amount = amount/10000 WHERE currency_id = '.ExchangeRates::BYN_ID;
        if($this->sqlExecute($sql)===false)
        {
            throw new ServerErrorHttpException('Acts');
        }

        $sql = 'UPDATE '.ActServices::tableName().' as1 
        LEFT JOIN '.Acts::tableName().' as t ON t.id = as1.act_id
        SET as1.amount = as1.amount/10000
        WHERE t.currency_id = '.ExchangeRates::BYN_ID;
        if(!$this->sqlExecute($sql))
        {
            throw new ServerErrorHttpException('Act services');
        }

        $sql = 'UPDATE '.ActToPayments::tableName().' ap 
        LEFT JOIN '.Payments::tableName().' as p ON p.id = ap.payment_id
        SET ap.amount = ap.amount/10000 
        WHERE p.currency_id = '.ExchangeRates::BYN_ID;
        if(!$this->sqlExecute($sql))
        {
            throw new ServerErrorHttpException('Acts to payment');
        }
    }

    protected function bonusScheme()
    {
        $sql = 'UPDATE '.BonusSchemeService::tableName().' set cost = cost/10000 WHERE  cost IS NOT NULL';
        if($this->sqlExecute($sql)===false)
        {
            throw new ServerErrorHttpException('Bonus scheme service');
        }
        $sql = 'UPDATE '.BonusSchemeServiceHistory::tableName().' set cost = cost/10000 WHERE  cost IS NOT NULL';
        if($this->sqlExecute($sql)===false)
        {
            throw new ServerErrorHttpException('Bonus scheme service');
        }
    }

    protected function partner()
    {
        $sql = 'UPDATE '.PartnerWithdrawalRequest::tableName().' set amount = amount/10000 WHERE  currency_id = 2';
        if($this->sqlExecute($sql)===false)
        {
            throw new ServerErrorHttpException('partner withdrawal ');
        }

        $sql = 'UPDATE '.PartnerPurse::tableName().' set amount = amount/10000';
        if($this->sqlExecute($sql)===false)
        {
            throw new ServerErrorHttpException('partner withdrawal ');
        }

        $sql = 'UPDATE '.PartnerPurseHistory::tableName().' set amount = amount/10000';
        if($this->sqlExecute($sql)===false)
        {
            throw new ServerErrorHttpException('partner withdrawal ');
        }

    }

    protected function bills()
    {
        $sql = 'UPDATE '.Bills::tableName().' set amount = amount/10000';
        if($this->sqlExecute($sql)===false)
        {
            throw new ServerErrorHttpException('bills');
        }
    }

    protected function buserBonus()
    {
        $sql = 'UPDATE '.BUserBonus::tableName().' set amount = amount/10000 WHERE currency_id IS NULL OR currency_id = 2';
        if($this->sqlExecute($sql)===false)
        {
            throw new ServerErrorHttpException('buser bonus');
        }
    }

    protected function config()
    {
        $sql = 'UPDATE '.Config::tableName().' set value = value/10000 WHERE alias IN ("min_bill_amount","pp_max","qh_rate")';
        if($this->sqlExecute($sql)===false)
        {
            throw new ServerErrorHttpException('config');
        }
    }
    protected function userPaymentsRecord()
    {
        $sql = 'UPDATE '.BUserPaymentRecords::tableName().' set amount = amount/10000';
        if($this->sqlExecute($sql)===false)
        {
            throw new ServerErrorHttpException('config');
        }
    }

}