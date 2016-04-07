<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 9.2.16
 * Time: 12.07
 */

namespace common\components\calendar;


use common\models\CalendarDays;
use common\models\managers\CalendarDaysManager;

class Calendar
{
	CONST
		DEFAULT_WORK_HOUR = 8;  //рабочее время по умолчанию

	CONST
		WORK_DAY = 'wd',
		SHORT_DAY = 'shd',
		HOLIDAY_DAY = 'hd',
		EXT_DAY = 'ed';

	protected
		$defaultHolidays = [    //праздники по умолчанию

		],
		$month = [
			1 => 'Январь',
			2 => 'Февраль',
			3 => 'Март',
			4 => 'Апрель',
			5 => 'Май',
			6 => 'Июнь',
			7 => 'Июль',
			8 => 'Август',
			9 => 'Сентябрь',
			10 => 'Октябрь',
			11 => 'Ноябрь',
			12 => 'Декабрь'
		],
		$holidayArr = [6,7];    //указываем какие дни недели являются выходными по умолчанию 6 - суббота, 7 - воскресенье

	/**
	 * @param $year
	 * @return array
	 */
	public function getCalendarForYearArray($year)
	{
		$arReturn = [];
		$arDays = CalendarDaysManager::getDaysInBDForYear($year);
		$i=1;   //январь
		while($i <= 12) //пройдем по месяцам
		{
			$days = [];     //собираем массив дней по недял с заполнением
			$workDays = 0;  //всего рабочих дней в месяце
			$holiday = 0;   //всего выходных в месяце
			$clockRate = 0; //всего нормачасов за месяц
			$prefixDate = $year.'-'.str_pad($i,2,0,STR_PAD_LEFT);   //добавляем ведущие нули
			$firstMonthDay = $prefixDate.'-01'; //первый день месяца
			$totalDays = (int)date('t',strtotime($firstMonthDay));    //всего дней в месяце
			$iWeek = 0;     //номер недели в месяце

			$N = (int)date('N',strtotime($firstMonthDay));  //порядковый номер дня недели 1-понедельник; 7-воскресенье
			$fillNum = 7-(8-$N);   //считаем кол-во элементов
			unset($N);
			if($fillNum > 0)
				$days[$iWeek] = array_fill(0, $fillNum, $this->fillDay(NULL,NULL,NULL,NULL,NULL,TRUE));    //заполняем пустыми значениями

			for($t = 1;$t <= $totalDays;$t++)
			{
				$dayTmpDate = $prefixDate.'-'.str_pad($t,2,0,STR_PAD_LEFT); //добавляем ведущие нули
				if(isset($arDays[$dayTmpDate]))
				{
					/** @var CalendarDays $model */
					$model = $arDays[$dayTmpDate];

					if($model->type == CalendarDays::TYPE_HOLIDAY)
					{
						$days[$iWeek][] = $this->fillDay($dayTmpDate,$t,CalendarDays::TYPE_HOLIDAY,NULL,$model->description);
						$holiday++;
					}else{
						$days[$iWeek][] = $this->fillDay($dayTmpDate,$t,self::WORK_DAY,(int)$model->work_hour,$model->description);
						$workDays++;
						$clockRate+=(int)$model->work_hour;
					}
				}else{
					$dayNum = (int)date('N',strtotime($dayTmpDate));
					if(in_array($dayNum,$this->holidayArr))
					{
						$days[$iWeek][] = $this->fillDay($dayTmpDate,$t,CalendarDays::TYPE_HOLIDAY,NULL);
						$holiday++;
					}else{
						$days[$iWeek][] = $this->fillDay($dayTmpDate,$t,self::WORK_DAY,self::DEFAULT_WORK_HOUR);
						$workDays++;
						$clockRate+=(int)self::DEFAULT_WORK_HOUR;
					}
				}
				//добавяем неделю. смотрим по воскресенью
				$N = (int)date('N',strtotime($dayTmpDate));
				if($N == 7)
					$iWeek++;
			}


			if(isset($days[$iWeek]) &&  count($days[$iWeek]) != 7)
			{
				$cdw = 7 - (int)count($days[$iWeek]);
				for($j =0;$j < $cdw ;$j++) {
					$days[$iWeek][] = $this->fillDay(null,NULL, NULL, NULL, NULL, TRUE);
				}
			}

			$arReturn [] = [
				'month' => $this->month[$i],
				'days' => $days,
				'workDay' => $workDays,
				'holiday' => $holiday,
				'clockRate' => $clockRate
			];
			$i++;
		}

		return $arReturn;
	}

	/**
	 * @param $dayNum
	 * @param null $dayType
	 * @param int $workHour
	 * @param string $description
	 * @param bool|FALSE $empty
	 * @return array
	 */
	protected function fillDay($date,$dayNum,$dayType = NULL,$workHour = self::DEFAULT_WORK_HOUR ,$description = '',$empty = FALSE)
	{
		$type = '';
		if($dayType == CalendarDays::TYPE_HOLIDAY)
		{
			$type = self::HOLIDAY_DAY;
		}else{
			if($workHour == self::DEFAULT_WORK_HOUR)
			{
				$type = self::WORK_DAY;
			}elseif($workHour > self::DEFAULT_WORK_HOUR){
				$type = self::EXT_DAY;
			}elseif($workHour < self::DEFAULT_WORK_HOUR)
			{
				$type = self::SHORT_DAY;
			}
		}

		return [
			'date' => $date,
			'empty' => $empty,
			'dayNum' => $dayNum,
			'workHour' => $workHour,
			'type' => $type,
			'description' => $description
		];

	}


}