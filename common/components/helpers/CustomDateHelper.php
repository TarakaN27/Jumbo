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

    /**
     * Is current month
     * @param $time
     * @return bool
     */
    public static function isCurrentMonth($time)
    {
        $beginMonth = CustomHelper::getBeginMonthTime(time());
        return $beginMonth <= $time;
    }

    /**
     * Проверяем является ли время $time текущим днем
     * @param $time
     * @return bool
     */
    public function isCurrentDay($time)
    {
        if(!is_numeric($time))
            $time = strtotime($time);

        $dateToday = new \DateTime('NOW');
        $dateToday->setTime(0,0,0);

        $dateCheck = new \DateTime();
        $dateCheck->setTimestamp($time);
        $dateCheck->setTime(0,0,0);
        $iterval = $dateToday->diff($dateCheck);

        return $iterval->days == 0;
    }

    /**
     * @param $str
     * @return mixed
     */
    public static function convertEnToRusMonth($str)
    {
        $arSearch = [
            'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December'
        ];

        $arReplace = [
            'Январь',
            'Февраль',
            'Март',
            'Апрель',
            'Май',
            'Июнь',
            'Июль',
            'Август',
            'Сентябрь',
            'Октябрь',
            'Ноябрь',
            'Декабрь'
        ];

        return str_replace($arSearch,$arReplace,$str);
    }

    /**
     * @param $date   25-06-2016
     * @return int
     */
    public static function isDateBeforeOrAfterDate($date,$timeCheck = NULL)
    {
        $time = strtotime($date.' 00:00:00');
        if(is_null($timeCheck))
            $timeCheck = time();
        
        if($timeCheck >= $time)
        {
            return 1;
        }else{
            return 0;
        }
    }

}