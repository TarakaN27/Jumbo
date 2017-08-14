<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 06.07.15
 */
namespace common\components\ExchangeRates;

class ExchangeRatesNBRB extends AbstractExchangeRates
{
    public
        $codeID,
        $date = NULL;

    protected
        $time = NULL,
        $internalCurrCode = [
            '980' => '290',         //гривна
            '840' => '145',         //usd
            '978' => '292',          //euro
            '643' => '298'          //ruble
        ];

    /**
     * @param $codeID
     */
    public function __construct($codeID = NULL,$time = null)
    {
        $this->time = is_null($time) ? time() : $time; //текущая дата
        $this->codeID = $codeID;
        $this->url = 'http://www.nbrb.by/Services/XmlExRates.aspx?ondate='.date('m',  $this->time) . '/' . date('d',  $this->time) . '/' . date('Y',  $this->time);
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
            foreach($sxml->Currency as $ar) {
                if($ar->NumCode == $this->codeID) {
                    $scale = (float)$ar->Scale;
                    return $this->getRateAfterDenomination((float)$ar->Rate/$scale, $this->time);
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
       // try{
            $sxml = $this->loadFile();
            if(!is_object($sxml)) {
                return NULL;
            }
            foreach($sxml->Currency as $ar) {
                $scale = (float)$ar->Scale;
                $result[(int)$ar->NumCode] = $this->getRateAfterDenomination((float)$ar->Rate/$scale,$this->time);
            }
      //  }catch (\Exception $e)
      //  {

      //  }
        return $result;
    }

    /**
     * @param $iCurrId
     * @param $beginDate
     * @param $endDate
     * @return array|null
     */
    public function getCurrencyRateByPeriod($iCurrId,$beginDate,$endDate)
    {
        $iCurrId = $this->internalCurrCode[$iCurrId];
        $fromDate = date('m/d/Y',$beginDate);
        $toDate = date('m/d/Y',$endDate);
        $this->url = 'http://www.nbrb.by/Services/XmlExRatesDyn.aspx?curId='.$iCurrId.'&fromDate='.$fromDate.'&toDate='.$toDate;
        $sxml = $this->loadFile();
        if(!is_object($sxml)){
            return NULL;
        }
        $arResult = [];

        foreach ($sxml->Record as $items)
        {
            $date = (string)$items->attributes()->Date;
            $dateTmp = (float)$items->Rate;
            $arResult [date('Y-m-d',strtotime($date))] = $this->getRateAfterDenomination($dateTmp,$this->time);
        }

        return $arResult;
    }

    public function getCurrencyRateByDate($date)
    {
        $onDate = date('m/d/Y',$date);
        $this->url = 'http://www.nbrb.by/Services/XmlExRates.aspx?ondate='.$onDate;
        $sxml = $this->loadFile();
        if(!is_object($sxml)){
            return NULL;
        }
        $arResult = [];

        foreach ($sxml->Currency as $items)
        {
            $rateId = $items->NumCode;
            $dateTmp = (float)$items->Rate;
            $arResult[(string)$rateId] = $this->getRateAfterDenomination($dateTmp,$this->time);
        }

        return $arResult;
    }


} 