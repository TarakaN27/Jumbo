<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 06.07.15
 */

namespace common\components\ExchangeRates;

use common\components\helpers\CustomDateHelper;


abstract class AbstractExchangeRates {

    CONST
        CORRECTION_FACTOR = 10000;

    protected
        $url;
    /**
     * @return \SimpleXMLElement
     */
    protected function loadFile()
    {
        return simplexml_load_file($this->url, NULL, TRUE);
    }

    /**
     * Коректируем курс валюты после деноминации
     * @param $rate
     * @return mixed
     */
    protected function getRateAfterDenomination($rate,$time = NULL)
    {
        if(CustomDateHelper::isDateBeforeOrAfterDate('01-07-2016',$time))
        {
            return $rate*self::CORRECTION_FACTOR;
        }else{
            return $rate;
        }
    }

} 