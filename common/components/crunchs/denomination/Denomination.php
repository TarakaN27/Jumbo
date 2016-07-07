<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 6.7.16
 * Time: 14.55
 */

namespace common\components\crunchs\denomination;


use common\models\ExchangeCurrencyHistory;
use common\models\ExchangeRates;
use common\models\PaymentRequest;
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
        //#1
        //echo 'services</br>'.PHP_EOL;
        //$this->services();
        //echo ' end services</br>'.PHP_EOL;

        //#2
        //echo 'exchange rate</br>'.PHP_EOL;
        //$this->exchangeRate();
        //echo 'end exchange rate</br>'.PHP_EOL;

        //#3
        //echo 'Payments request</br>'.PHP_EOL;
        //$this->paymentRequest();
        //echo ' end services</br>'.PHP_EOL;






        die('end');
    }

    /**
     * @return bool
     * @throws ServerErrorHttpException
     */
    protected function services()
    {
        $sql = 'UPDATE '.Services::tableName().' set rate=rate/10000';
        if(!$this->sqlExecute($sql))
        {
            throw new ServerErrorHttpException('Service query error');
        }
        return TRUE;
    }

    protected function exchangeRate()
    {
        if(!ExchangeRates::updateAll(['code' => 'BYN','cbr' => '933'],['id' => ExchangeRates::BYN_ID]))
            throw new ServerErrorHttpException('Exchange rate change code');

        $sql = 'UPDATE '.ExchangeRates::tableName().' set nbrb_rate = nbrb_rate/10000 WHERE id != '.ExchangeRates::BYN_ID;
        if(!$this->sqlExecute($sql))
        {
            throw new ServerErrorHttpException('Exchange rate');
        }

        $sql = 'UPDATE '.ExchangeCurrencyHistory::tableName().' set rate_nbrb = rate_nbrb/10000,old_rate_nbrb = old_rate_nbrb/10000 WHERE currency_id != '.ExchangeRates::BYN_ID;
        if(!$this->sqlExecute($sql))
        {
            throw new ServerErrorHttpException('Exchange rate history');
        }
    }

    protected function paymentRequest()
    {
        $sql = 'UPDATE '.PaymentRequest::tableName().' set pay_summ = pay_summ/10000 WHERE currency_id = '.ExchangeRates::BYN_ID;
        if(!$this->sqlExecute($sql))
        {
            throw new ServerErrorHttpException('Exchange rate');
        }



    }

}