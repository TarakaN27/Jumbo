<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 06.07.15
 */

namespace common\components\ExchangeRates;

use common\components\helpers\CustomDateHelper;


abstract class AbstractExchangeRates
{

    CONST
        CORRECTION_FACTOR = 10000;

    protected
        $url;

    /**
     * @return \SimpleXMLElement
     */
    protected function loadFile()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //Устанавливаем параметр, чтобы curl возвращал данные, вместо того, чтобы выводить их в браузер.
        curl_setopt($ch, CURLOPT_URL, $this->url);
        $xml = curl_exec($ch);
        curl_close($ch);
        return simplexml_load_string($xml, NULL, TRUE);
    }

    /**
     * Коректируем курс валюты после деноминации
     * @param $rate
     * @return mixed
     */
    protected function getRateAfterDenomination($rate, $time = NULL)
    {
        if (CustomDateHelper::isDateBeforeOrAfterDate('01-07-2016', $time)) {
            return $rate;
        } else {
            return round($rate / self::CORRECTION_FACTOR);
        }
    }

} 