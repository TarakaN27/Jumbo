<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 06.07.15
 */

namespace common\components\ExchangeRates;


class ExchangeRatesCBRF extends AbstractExchangeRates{

    public
        $date,
        $codeID;

    private
        $url = 'http://www.cbr.ru/scripts/XML_daily.asp?date_req=';

    public function __construct($codeID = NULL)
    {
        $time = time(); //текущая дата
        $this->codeID = $codeID;
        $this->url .= date('d', $time) . '/' . date('m', $time) . '/' . date('Y', $time);
    }

    /**
     * @return int|null
     */
    public function makeRequest()
    {
        try{
            $sxml = simplexml_load_file($this->url, NULL, TRUE);

            if(!is_object($sxml)) {
                return NULL;
            }
            foreach($sxml->Valute as $ar) {
                if($ar->NumCode == $this->codeID)
                    return  round($this->convertValue($ar->Value)/$ar->Nominal,4);
            }
            return NULL;
        }catch (\Exception $e)
        {
            return NULL;
        }
    }

    public function convertValue($val)
    {
        return round(str_replace(',','.',$val),4);

    }


} 