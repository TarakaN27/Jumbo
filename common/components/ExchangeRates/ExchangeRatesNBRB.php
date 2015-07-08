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

    private
        $url = 'http://www.nbrb.by/Services/XmlExRates.aspx?ondate=';

    /**
     * @param $codeID
     */
    public function __construct($codeID = NULL)
    {
        $time = time(); //текущая дата
        $this->codeID = $codeID;
        $this->url .= date('m', $time) . '/' . date('d', $time) . '/' . date('Y', $time);
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
            foreach($sxml->Currency as $ar) {
                if($ar->NumCode == $this->codeID)
                    return (int) $ar->Rate;
           }
           return NULL;
        }catch (\Exception $e)
        {
            return NULL;
        }
    }
} 