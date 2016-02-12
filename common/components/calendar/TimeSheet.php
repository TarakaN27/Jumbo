<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 11.2.16
 * Time: 13.41
 */

namespace common\components\calendar;

use backend\models\BUser;
use common\models\CrmTask;
use common\models\CrmTaskLogTime;
use common\models\CUser;
use common\models\managers\CalendarDaysManager;
use common\models\WorkDay;
use yii\base\InvalidParamException;
use yii\helpers\Html;
use yii\web\NotFoundHttpException;
use common\models\CalendarDays;

class TimeSheet
{
	protected
		$userID,
		$startDate,
		$endDate;

	protected
		$userByTask = [],
		$userByWorkDay = [],
		$userOther = [];

	/**
	 * @param $userID
	 * @param $startDate
	 * @param $endDate
	 */
	public function __construct($userID,$startDate,$endDate)
	{
		if(empty($userID) || empty($startDate) || empty($endDate))
		{
			throw new InvalidParamException('Required params not found');
		}

		if($startDate > $endDate)
			throw new InvalidParamException('Invalid period');

		$this->userID = (int)$userID;
		$this->startDate = $startDate;
		$this->endDate = $endDate;
	}

	/**
	 * получаем данные для построения timesheet
	 * @return array
	 * @throws NotFoundHttpException
	 */
	public function getTimesheetData()
	{
		/** @var BUser $obUser */
		$obUser = $this->getUser();     //получаем пользователя(чтобы выяснить тип time трекинга)
		$arCDays = CalendarDaysManager::getDaysForRange($this->startDate,$this->endDate); //получаем не стандартные дни из календаря

		$arLogTaskTime = $this->getLogTaskTime($obUser->id);
		$arLogWorkDay = [];

		$iTotalWorkDayTime = 0;
		$arWorkDayTooltip = [];
		//если учет по рабочему дню
		if($obUser->log_work_type == $obUser::LOG_WORK_TYPE_TIMER)
		{
			$arLogWorkDay = $this->getLogWorkDayTime($obUser->id);

			foreach($arLogWorkDay as &$log)
			{
				$iTotalWorkDayTime+=(int)$log->spent_time;
				$arWorkDayTooltip[$log->id] = \Yii::t('app/reports','Begin day').': '.\Yii::$app->formatter->asDatetime($log->begin_time).';'.
					\Yii::t('app/reports','End day').': '.\Yii::$app->formatter->asDatetime($log->end_time).';'.
					\Yii::t('app/reports','Description').': '.Html::encode($log->description);

				$log->spent_time = round($log->spent_time/3600,2);
			}

			$iTotalWorkDayTime = round($iTotalWorkDayTime/3600,2);
		}

		//учет по задачам
		$arTaskTimeByDays = []; //собираем массив с задачами и подтраченым временем по датам
		$arTotalLogTime = [];
		$iTotalLogTime = 0;
		$iTotalNeed = 0;
		foreach($arLogTaskTime as $logTime)
		{
			if(isset($arTaskTimeByDays[$logTime->task_id][$logTime->log_date]))
			{
				$arTaskTimeByDays[$logTime->task_id][$logTime->log_date]+=(int)$logTime->spend_time;
				$arTotalLogTime[$logTime->log_date]+=(int)$logTime->spend_time;
			}else{
				$arTaskTimeByDays[$logTime->task_id][$logTime->log_date]=(int)$logTime->spend_time;
				$arTotalLogTime[$logTime->log_date]=(int)$logTime->spend_time;
			}

			$iTotalLogTime+=(int)$logTime->spend_time;
		}

		$arTaskReal = $this->getTaskReal(array_keys($arTaskTimeByDays));    //получаем задачи

		$arCmp = [];
		foreach($arTaskReal as $item)
			if(!empty($item->cmp_id) && !in_array($item->cmp_id,$arCmp))
				$arCmp [] = $item->cmp_id;

		$arCmp = $this->getCmp($arCmp);

		//собираем календарь
		$arDays = $this->getArDays();
		foreach($arDays as $key => &$day)
		{
			if(isset($arTotalLogTime[$key]))
				$day['total'] = $arTotalLogTime[$key];
			else
				$day['total'] = 0;

			if(isset($arLogWorkDay[$key]))
				$day['wdTotal'] = $arLogWorkDay[$key]->spent_time;
			else
				$day['wdTotal'] = 0;

			if(isset($arCDays[$key]))
			{
				/** @var CalendarDays $model */
				$model = $arCDays[$key];
				if($model->type == CalendarDays::TYPE_HOLIDAY)
				{
					$day['class'] = Calendar::HOLIDAY_DAY;
					$day['need'] = 0;
				}else{
					$day['class'] = Calendar::WORK_DAY;
					$day['need'] = (int)$model->work_hour;
					$iTotalNeed+=(int)$model->work_hour;
				}
			}else{
				$dayNum = (int)date('N',strtotime($key));
				if(in_array($dayNum,[6,7]))
				{
					$day['class'] = Calendar::HOLIDAY_DAY;
					$day['need'] = 0;
				}else{
					$day['class'] = Calendar::WORK_DAY;
					$day['need'] = (int)Calendar::DEFAULT_WORK_HOUR;
					$iTotalNeed+=(int)Calendar::DEFAULT_WORK_HOUR;
				}
			}
		}

		$arReturnTask = [];
		foreach($arTaskReal as $key => $task)
		{
			if(!empty($task->cmp_id) && isset($arCmp[$task->cmp_id]))
			{
				$arReturnTask[$task->cmp_id][] =
					[
						'taskID' => $task->id,
						'title' => $task->title,
						'status' => $task->status,
						'log' => isset($arTaskTimeByDays[$key]) ? $arTaskTimeByDays[$key] : []
					];
			}
			else{
				$arReturnTask['no_cmp'][] = [
					'taskID' => $task->id,
					'title' => $task->title,
					'status' => $task->status,
					'log' => isset($arTaskTimeByDays[$key]) ? $arTaskTimeByDays[$key] : []
				];
			}
		}

		return [
			'arDays' => $arDays,
			'logWorkTypeID' => $obUser->log_work_type,
			'logWorkTypeStr' => $obUser->getLogWorkTypeStr(),
			'tasks' => $arReturnTask,
			'iTotalNeed' => $iTotalNeed,
			'iTotalLogTime' => round($iTotalLogTime/3600,2),
			'arCmp' => $arCmp,
			'arLogWorkDay' => $arLogWorkDay,
			'iTotalWorkDayTime' => $iTotalWorkDayTime,
			'arWorkDayTooltip' => $arWorkDayTooltip
		];
	}


	public function getUsersTimeSheetData()
	{
		$arUsers = $this->getAllUsers();   //получаем всех пользователей;
		$iTotalNeed = 0;
		$arDays = $this->getCalendardays();
		$arLogTask = $this->getLogTaskForUsers(array_keys($this->userByTask));
		$arLogWorkDay = $this->getLogWorkDayForUsers(array_keys($this->userByWorkDay));
		$arTotal = [];
		foreach($arLogTask as $key => &$logTask)
		{
			foreach($logTask as &$logTime)
			{
				if(isset($arTotal[$key])) {
					$arTotal[$key] += $logTime;
				}else{
					$arTotal[$key] = $logTime;
				}
				$logTime = round($logTime/3600,2);
			}
		}

		foreach($arLogWorkDay as $key => &$logWorkDay)
		{
			foreach($logWorkDay as &$workDay)
			{
				if(isset($arTotal[$key])) {
					$arTotal[$key] += $workDay;
				}else{
					$arTotal[$key] = $workDay;
				}
				$workDay = round($workDay/3600,2);
			}
		}

		foreach($arDays as $day)
		{
			$iTotalNeed+=(int)$day['need'];
		}

		foreach($this->userByTask as $key => $value)
		{
			if(!isset($arTotal[$key]))
				$arTotal[$key] = 0;
		}

		foreach($this->userByWorkDay as $key => $item)
		{
			if(!isset($arTotal[$key]))
				$arTotal[$key] = 0;
		}

		return [
			'iTotalNeed' => $iTotalNeed,
			'arDays' => $arDays,
			'arTotal' => $arTotal,
			'arLogWorkDay' => $arLogWorkDay,
			'arLogTask' => $arLogTask,
			'arUsersByTask' => $this->userByTask,
			'arUsersByWorkDay' => $this->userByWorkDay,
			'arUsersOther' => $this->userOther
		];
	}

	protected function getLogWorkDayForUsers(array $userIds)
	{
		if(empty($userIds))
			return [];

		$tmp =  WorkDay::find()
			->select(['log_date','spent_time','buser_id'])
			->where(['buser_id' => $userIds])
			->andWhere('log_date BETWEEN :startDate AND :endDate')
			->params([
				':startDate' => $this->startDate,
				':endDate' => $this->endDate
			])
			->all();

		$arReturn = [];
		foreach($tmp as $item)
		{
			if(isset($arReturn[$item->buser_id][$item->log_date]))
				$arReturn[$item->buser_id][$item->log_date]+=(int)$item->spent_time;
			else
				$arReturn[$item->buser_id][$item->log_date]=(int)$item->spent_time;
		}

		return $arReturn;
	}

	/**
	 * Получаем массив с отработанными часами по датам
	 * @param array $userIds
	 * @return array
	 */
	protected function getLogTaskForUsers(array $userIds)
	{
		if(empty($userIds))
			return [];

		$tmp = CrmTaskLogTime::find()
			->select(['buser_id','spend_time','log_date'])
			->where(['buser_id' => $userIds])
			->andWhere('log_date BETWEEN :startDate AND :endDate')
			->params([
				':startDate' => $this->startDate,
				':endDate' => $this->endDate
			])
			->all();

		$arReturn = [];
		foreach($tmp as $item)
		{
			if(isset($arReturn[$item->buser_id][$item->log_date]))
			{
				$arReturn[$item->buser_id][$item->log_date]+=(int)$item->spend_time;
			}else{
				$arReturn[$item->buser_id][$item->log_date]=(int)$item->spend_time;
			}
		}

		return $arReturn;
	}


	/**
	 * @return array
	 */
	protected function getAllUsers()
	{
		$tmp = BUser::getAllMembersObj();
		$arReturn = [];
		/** @var BUser $t */
		foreach($tmp as $t)
		{
			switch($t->log_work_type){
				case BUser::LOG_WORK_TYPE_TASK:
					$this->userByTask[$t->id] = $t;
					break;
				case BUser::LOG_WORK_TYPE_TIMER:
					$this->userByWorkDay[$t->id] = $t;
					break;
				default:
					$this->userOther[$t->id] = $t;
					break;
			}
			$arReturn[$t->id] = $t;
		}
		return $arReturn;
	}

	protected function getCalendardays()
	{
		$arCDays = CalendarDaysManager::getDaysForRange($this->startDate,$this->endDate); //получаем не стандартные дни из календаря

		//собираем календарь
		$arDays = $this->getArDays();
		foreach($arDays as $key => &$day)
		{
			if(isset($arCDays[$key]))
			{
				/** @var CalendarDays $model */
				$model = $arCDays[$key];
				if($model->type == CalendarDays::TYPE_HOLIDAY)
				{
					$day['class'] = Calendar::HOLIDAY_DAY;
					$day['need'] = 0;
				}else{
					$day['class'] = Calendar::WORK_DAY;
					$day['need'] = (int)$model->work_hour;
				}
			}else{
				$dayNum = (int)date('N',strtotime($key));
				if(in_array($dayNum,[6,7]))
				{
					$day['class'] = Calendar::HOLIDAY_DAY;
					$day['need'] = 0;
				}else{
					$day['class'] = Calendar::WORK_DAY;
					$day['need'] = (int)Calendar::DEFAULT_WORK_HOUR;
				}
			}
		}
		return $arDays;
	}


	/**
	 * @return null|static
	 * @throws NotFoundHttpException
	 */
	protected function getUser()
	{
		$model = BUser::findOne($this->userID);
		if(empty($model))
			throw new NotFoundHttpException('User not found');

		return $model;
	}

	/**
	 * Получаем время по задачам
	 * @param $userID
	 * @return mixed
	 */
	protected function getLogTaskTime($userID)
	{
		return CrmTaskLogTime::find()
			->select(['id','task_id','spend_time','log_date'])
			->where(['buser_id' => $userID])
			->andWhere('log_date BETWEEN :startDate AND :endDate')
			->params([
				':startDate' => $this->startDate,
				':endDate' => $this->endDate
			])
			->all();
	}

	/**
	 * Получаем время по рабочим дням
	 * @param $userID
	 * @return mixed
	 */
	protected function getLogWorkDayTime($userID)
	{
		$tmp =  WorkDay::find()
			->select(['id','log_date','spent_time','begin_time','end_time','description'])
			->where(['buser_id' => $userID])
			->andWhere('log_date BETWEEN :startDate AND :endDate')
			->params([
				':startDate' => $this->startDate,
				':endDate' => $this->endDate
			])
			->all();
		$arReturn = [];
		foreach($tmp as $t)
		{
			$arReturn[$t->log_date] = $t;
		}

		return $arReturn;
	}

	/**
	 * @param array $arTaskIds
	 * @return array
	 */
	protected function getTaskReal(array $arTaskIds)
	{
		$arReturn = [];
		$tmp = CrmTask::find()
			->select(['id','title','status','cmp_id'])
			->where(['id' => $arTaskIds])
			->all();

		foreach($tmp as $t)
			$arReturn[$t->id] = $t;

		return $arReturn;
	}

	/**
	 * @param array $arCmpIds
	 * @return array
	 */
	protected function getCmp(array $arCmpIds)
	{
		$tmp = CUser::find()
			->with('requisites')
			->select(['id','requisites_id'])
			->where(['id' => $arCmpIds])
			->all();

		$arReturn = [];
		foreach($tmp as $t)
			$arReturn[$t->id] = $t->getInfo();

		return $arReturn;
	}

	/**
	 * @return array
	 */
	protected function getArDays()
	{
		$obStart = new \DateTime($this->startDate);

		$arResult = [];

		while($obStart->format('Y-m-d') != $this->endDate)
		{
			$arResult[$obStart->format('Y-m-d')] = [
				'title' => $obStart->format('d')
			];
			$obStart->modify('+1 day');
		}
		$arResult[$obStart->format('Y-m-d')] = [
			'title' => $obStart->format('d')
		];
		return $arResult;
	}
}