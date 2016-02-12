<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 9.2.16
 * Time: 10.38
 */

namespace common\models\managers;


use common\models\CalendarDays;

class CalendarDaysManager extends CalendarDays
{
	/**
	 * @param $year -- integer ex.2016
	 * @return mixed
	 */
	public static function getDaysInBDForYear($year)
	{
		$arDays = self::find()  //выбираем все даты, которые записаны в бд
			->where('date BETWEEN :startDate AND :endDate')
			->params([
				':startDate' => $year.'-01-01',
				':endDate' => $year.'-12-31'
			])
			->all();

		$arReturn = [];
		foreach($arDays as $day)
			$arReturn[$day->date] = $day;

		return $arReturn;
	}

	/**
	 * @param $begin -- 2016-01-01 Y-m-d
	 * @param $end -- 2016-02-01
	 * @return array
	 */
	public static function getDaysForRange($begin,$end)
	{
		$arDays = self::find()  //выбираем все даты, которые записаны в бд
		->where('date BETWEEN :startDate AND :endDate')
			->params([
				':startDate' => $begin,
				':endDate' => $end
			])
			->all();

		$arReturn = [];
		foreach($arDays as $day)
			$arReturn[$day->date] = $day;

		return $arReturn;
	}
}