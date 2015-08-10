<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 10.08.15
 */

namespace common\components\ExchangeRates;


class ExchangeRatesObmennikBY extends AbstractExchangeRates{

    protected
        $bankID;

    public function __construct()
    {
        $this->url = 'http://www.obmennik.by/xml/kurs.xml';
    }


    public function getCurrencyUSD()
    {
        $data = json_decode(json_encode($this->loadFile(), 1));
        $prop = 'bank-id';
        $usd = 0;
        $rub = 0;

        foreach($data->$prop as $item)
            if($item->idbank == $this->bankID)
            {
                $usd = $item->usd->buy;
                $rub = $item->rur->buy;
                break;
            }

        if($usd != 0 && $rub != 0)
            return [
                'bur' => $usd,
                'rur' => $usd/$rub
            ];
        else
            return NULL;
    }



    /**
     * @param $id
     */
    public function setBankID($id)
    {
        $this->bankID  = (int)$id;
    }
} 