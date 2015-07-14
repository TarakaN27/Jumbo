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

    public function __construct($codeID = NULL)
    {
        $time = time(); //текущая дата
        $this->codeID = $codeID;
        $this->url = 'http://www.cbr.ru/scripts/XML_daily.asp?date_req='.date('d', $time) . '/' . date('m', $time) . '/' . date('Y', $time);
    }

    /**
     * @return int|null
     */
    public function makeRequest()
    {
        try{
            $sxml = $this->loadFile();

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


    /**
     * @return array|null
     */
    public function getAllCurrency()
    {
        $result = [];
        //try{
            $sxml = $this->loadFile();
            if(!is_object($sxml)) {
                return NULL;
            }
            foreach($sxml->Valute as $ar) {
                $result[(int)$ar->NumCode] = round($this->convertValue($ar->Value)/$ar->Nominal,4);
            }
        //}catch (\Exception $e)
       // {

        //}
        return $result;
    }

    /**
     * @param $val
     * @return float
     */
    public function convertValue($val)
    {
        return round(str_replace(',','.',$val),4);

    }


} 