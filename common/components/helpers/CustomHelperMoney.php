<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 24.6.16
 * Time: 17.10
 */

namespace common\components\helpers;


class CustomHelperMoney
{
    CONST
        BYN_BUR_FACTOR = 10000;

    /**
     * @param $amount
     * @return float
     */
    public static function convertFromBynToBur($amount)
    {
        $amount = preg_replace("/\s/","",$amount);
        $amount = str_replace(',','.',$amount);
        $amount = (float)$amount;
        $amount = $amount*self::BYN_BUR_FACTOR;        //удалить после деноминации
        return $amount;
    }

    /**
     * @param $amount
     * @return float
     */
    public static function convertFromBurToByn($amount)
    {
        return $amount/self::BYN_BUR_FACTOR;
    }



}