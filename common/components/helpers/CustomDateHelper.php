<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 18.5.16
 * Time: 11.25
 */

namespace common\components\helpers;


class CustomDateHelper
{
    CONST
        DATE_MODIFY_MONTH = 'month',
        DATE_MODIFY_DAY = 'day';

    protected static function getInterval($time1,$time2)
    {
        $date1 = new \DateTime();
        $date1->setTimestamp($time1);
        $date2 = new \DateTime();
        $date2->setTimestamp($time2);
        return $date1->diff($date2);
    }

    /**
     * Get difference in days
     * @param $time1
     * @param $time2
     * @return int
     */
    public static function getDiffInDays($time1,$time2)
    {
        $interval = self::getInterval($time1,$time2);
        return (int)$interval->d;
    }

    /**
     * Get difference in week
     * @param $time1
     * @param $time2
     * @return mixed
     */
    public static function getDiffInWeeks($time1,$time2)
    {
        $interval = self::getInterval($time1,$time2);
        return floor((int)$interval->d/7);
    }

    /**
     * @param $time
     * @param $num
     * @param $entity (month, day)
     * @param string $sign
     * @return int
     */
    public static function dateModify($time,$num,$entity,$sign='+')
    {
        $obDate = new \DateTime();
        $obDate->setTimestamp($time);
        $obDate->modify($sign.$num.' '.$entity);
        return $obDate->getTimestamp();
    }
}