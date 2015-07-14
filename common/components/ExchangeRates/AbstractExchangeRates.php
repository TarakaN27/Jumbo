<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 06.07.15
 */

namespace common\components\ExchangeRates;


abstract class AbstractExchangeRates {
    protected
        $url;
    /**
     * @return \SimpleXMLElement
     */
    protected function loadFile()
    {
        return simplexml_load_file($this->url, NULL, TRUE);
    }
} 