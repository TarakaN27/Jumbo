<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 17.5.16
 * Time: 15.42
 */

namespace common\components\tasks;


use common\components\helpers\CustomDateHelper;
use common\components\helpers\CustomHelper;
use common\models\CrmTask;
use common\models\CrmTaskRepeat;
use common\models\managers\CrmTaskManager;
use yii\helpers\ArrayHelper;

class RecurringTask
{
    protected
        $iCurrentTime = NULL,
        $arTask = [],
        $arTaskIds = [];            //task ids for clone

    /**
     * RecurringTask constructor.
     * @param null $iTime
     */
    public function __construct($iTime = NULL)
    {
        $tmp = is_null($iTime) ? time() : $iTime;
        $this->iCurrentTime = CustomHelper::getBeginDayTime($tmp);      //установим время начала дня
    }

    /**
     * @return bool
     */
    function run()
    {
        $arRecurringTask = $this->getTasksRepeat();                 //get recurring tasks
        if(empty($arRecurringTask))
            return TRUE;
        $this->getTasks($arRecurringTask);                          //get tasks with last recurring update
        if(empty($this->arTask))
            return TRUE;
        $this->getTaskIdsForClone($arRecurringTask);                //get task ids for clone
        if(!empty($this->arTaskIds))
            $this->createTasks();
        
        return TRUE;
    }

    /**
     * @param array $arItem
     * @return array
     */
    protected function getTaskIdsForClone(array $arItem)
    {
        /** @var CrmTaskRepeat $item */
        foreach ($arItem as $item)
        {
            if(!array_key_exists($item->task_id,$this->arTask))       //check if task is find
                continue;

            if($item->end_type == CrmTaskRepeat::END_TYPE_COUNT_OCC && $item->counter_repeat >= $item->count_occurrences)
                continue;

            if($item->end_type == CrmTaskRepeat::END_TYPE_DATE && CustomHelper::getBeginDayTime($item->end_date) <= $this->iCurrentTime)
                continue;

            $iLastUpd = $this->arTask[$item->task_id];
            switch ($item->type)
            {
                case CrmTaskRepeat::TYPE_DAILY:
                    $this->processTypeDaily($item,$iLastUpd);
                    break;
                case CrmTaskRepeat::TYPE_WEEKLY:
                    $this->processTypeWeekly($item,$iLastUpd);
                    break;
                case CrmTaskRepeat::TYPE_MONTHLY:
                    $this->processMonthly($item,$iLastUpd);
                    break;
                default:
                    break;
            }
        }
        return $this->arTaskIds;
    }

    /**
     * Get last recurring update
     * @param array $arItem
     * @return array
     */
    protected function getTasks(array $arItem)
    {
        $arIds = ArrayHelper::getColumn($arItem,'task_id');             //getIds
        return $this->arTask = ArrayHelper::map(
            CrmTask::find()
                ->select(['id','recurring_last_upd'])
                ->where([
                    'id' => $arIds,
                    'repeat_task' => CrmTask::YES
                ])
                ->all(),
            'id','recurring_last_upd');
    }
    
    /**
     * @return mixed
     */
    protected function getTasksRepeat()
    {
        return CrmTaskRepeat::find()
            ->where('start_date <= :time AND (end_date > :time OR end_date is NULL)')
            ->params([
                ':time' => time()
            ])->all();
    }

    /**
     * @param CrmTaskRepeat $obTaskRepeat
     * @return int|null
     */
    protected function processTypeDaily(CrmTaskRepeat $obTaskRepeat,$iLastUpd)
    {
        if(empty($iLastUpd))
        {
            $this->arTaskIds = [$obTaskRepeat->task_id];
            return $obTaskRepeat->task_id;
        }

        $iDiffDays = CustomDateHelper::getDiffInDays($this->iCurrentTime,CustomHelper::getBeginDayTime($iLastUpd));
        $iDays = $obTaskRepeat->everyday_custom ? $obTaskRepeat->everyday_value : 1;

        if(!$iDays)
            return NULL;

        if($iDiffDays >= $iDays)
        {
            $this->arTaskIds = [$obTaskRepeat->task_id];
            return $obTaskRepeat->task_id;
        }

        return NULL;
    }

    /**
     * @param CrmTaskRepeat $obTaskRepeat
     * @param $iLastUpd
     * @return int|null
     */
    protected function processTypeWeekly(CrmTaskRepeat $obTaskRepeat,$iLastUpd)
    {
        $wd = $obTaskRepeat->getWeekDay();  //
        $currWD = date('w',$this->iCurrentTime);

        if(empty($iLastUpd) && (is_null($wd) || (!is_null($wd) && $wd == $currWD)))
        {
            $this->arTaskIds = [$obTaskRepeat->task_id];
            return $obTaskRepeat->task_id;
        }

        if(empty($obTaskRepeat->week))
            return NULL;

        $iW = (int)$obTaskRepeat->week;
        $iDiffWeek = CustomDateHelper::getDiffInWeeks($this->iCurrentTime,CustomHelper::getBeginDayTime($iLastUpd));

        if($iDiffWeek >= $iW)
        {
            if(is_null($wd) || (!is_null($wd) && $currWD == $wd))
            {
                $this->arTaskIds = [$obTaskRepeat->task_id];
                return $obTaskRepeat->task_id;
            }
        }

        return NULL;
    }

    /**
     * @param CrmTaskRepeat $obTaskRepeat
     * @param $iLastUpd
     * @return int|null
     */
    protected function processMonthly(CrmTaskRepeat $obTaskRepeat,$iLastUpd)
    {
        if(empty($iLastUpd))
        {
            $this->arTaskIds = [$obTaskRepeat->task_id];
            return $obTaskRepeat->task_id;
        }

        if(empty($obTaskRepeat->day) || empty($obTaskRepeat->month))
            return NULL;

        $dayX = CustomDateHelper::dateModify($iLastUpd,$obTaskRepeat->month,CustomDateHelper::DATE_MODIFY_MONTH);
        $dayX = CustomDateHelper::dateModify($dayX,$obTaskRepeat->day,CustomDateHelper::DATE_MODIFY_DAY);

        if($dayX <= $this->iCurrentTime)
        {
            $this->arTaskIds = [$obTaskRepeat->task_id];
            return $obTaskRepeat->task_id;
        }

        return NULL;
    }

    /**
     * @return bool
     * @throws \yii\web\NotFoundHttpException
     */
    protected function createTasks()
    {
        $arTasks = CrmTask::find()->where(['id' => $this->arTaskIds])->all();
        $arTasksIds = [];
        foreach ($arTasks as $item)
        {
            $item->isCreateRepeatTask = true;
            $taskID = $item->id;
            if(CrmTaskManager::cloneTask($item->id,true,$item))
                $arTasksIds [] = $taskID;
        }
        if($arTasksIds)
        {
            CrmTask::updateAll(['recurring_last_upd' => $this->iCurrentTime],['id' => $arTasksIds]);
            CrmTaskRepeat::updateAllCounters(['counter_repeat' => 1],['task_id' => $arTasksIds,'end_type' => CrmTaskRepeat::END_TYPE_COUNT_OCC]);
        }

        return TRUE;
    }

}