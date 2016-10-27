<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 06.07.15
 */

namespace common\components\ExchangeRates;


use common\components\helpers\CustomDateHelper;

class ExchangeRatesCBRF extends AbstractExchangeRates{


    CONST
        BYN_IN_CBR_CODE = 933,  //код деноминированной валюты после 1.07.2016
        BYR_IN_CBR_CODE = 974;  //код бел рубля в ЦБРФ

    public
        $date,
        $codeID;

    protected
        $time;

    public function __construct($codeID = NULL,$time = NULL)
    {
        $this->time = is_null($time) ? time() : $time;
        if(!CustomDateHelper::isDateBeforeOrAfterDate('01-07-2016',$this->time) && $codeID == self::BYN_IN_CBR_CODE)
        {
            $this->codeID = self::BYR_IN_CBR_CODE;
        }else
            $this->codeID = $codeID;
        $this->url = 'http://www.cbr.ru/scripts/XML_daily.asp?date_req='.date('d', $this->time) . '/' . date('m', $this->time) . '/' . date('Y', $this->time);
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
                if($ar->NumCode == $this->codeID) {
                    $tmp =round($this->convertValue($ar->Value) / $ar->Nominal, 6);
                    return $this->codeID == self::BYR_IN_CBR_CODE ? $this->getRateAfterDenomination($tmp,$this->time) : $tmp;
                }
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
                $tmp = round($this->convertValue($ar->Value)/$ar->Nominal,4);
                $result[(int)$ar->NumCode] =$this->codeID == self::BYR_IN_CBR_CODE ? $this->getRateAfterDenomination($tmp,$this->time) : $tmp;
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

    /**
     * Курс российского рубля в бел. рублях по ЦБРФ
     * @return float|null
     */
    public function getRURcurrencyInBur()
    {
        try{

            $sxml = $this->loadFile();

            if(!is_object($sxml)) {
                return NULL;
            }
            foreach($sxml->Valute as $ar) {
                $code = CustomDateHelper::isDateBeforeOrAfterDate('01-07-2016',$this->time) ? self::BYN_IN_CBR_CODE : self::BYR_IN_CBR_CODE;
                if($ar->NumCode == $code) {
                    return $this->getRateAfterDenomination(round($ar->Nominal / $this->convertValue($ar->Value), 6), $this->time);
                }
            }
            return NULL;
        }catch (\Exception $e)
        {
            return NULL;
        }
    }


} 