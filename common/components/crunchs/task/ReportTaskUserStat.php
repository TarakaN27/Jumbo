<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 26.5.16
 * Time: 12.59
 */

namespace common\components\crunchs\task;

use backend\models\BUser;
use common\components\helpers\CustomHelper;
use common\models\CrmTask;
use common\models\CrmTaskLogTime;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class ReportTaskUserStat
{

    public function userInfoTaskLoadBalance()
    {
        $arUser = BUser::find()->select(['id', 'fname', 'lname', 'mname'])->all();

        $arDates = ['2016-01-01','2016-02-01','2016-03-01','2016-04-01'];

        $arResultCountTasks = [];
        $arTaskLog = [];

        foreach ($arDates as $date)
        {
            $beginTime = CustomHelper::getBeginMonthTime(strtotime($date));
            $endTime = CustomHelper::getEndMonthTime(strtotime($date));
             $tmpTask = (new Query())
                ->select(['created_by', 'count(id) as t_count'])
                ->from(CrmTask::tableName())
                ->where('created_at >= :beginDate AND created_at<= :endDate')
                ->params(['beginDate' => $beginTime,'endDate' => $endTime])
                ->groupBy('created_by')
                ->all();
            $arResultCountTasks[$date] = ArrayHelper::map($tmpTask,'created_by','t_count');

            $tmpLog = (new Query())
                ->select(['t.created_by','sum(spend_time) as s_time'])
                ->from(CrmTaskLogTime::tableName().' log')
                ->leftJoin(CrmTask::tableName().' t','log.task_id = t.id')
                ->where('t.created_at >= :beginDate AND t.created_at<= :endDate')
                ->params(['beginDate' => $beginTime,'endDate' => $endTime])
                ->groupBy('t.created_by')
                ->all();
            $arTaskLog[$date] = ArrayHelper::map($tmpLog,'created_by','s_time');
        }

        $arResult = [];
        $arResult[0][] = 'Сотрудник';
        foreach ($arDates as $date)
        {
            $arResult[0][] = 'task_'.\Yii::$app->formatter->asDate($date);
        }
        foreach ($arDates as $date)
        {
            $arResult[0][] = 'time_'.\Yii::$app->formatter->asDate($date);
        }

        foreach ($arUser as $keyA => $obUser)
        {
            $key = $keyA+1;
            $arResult[$key][]= $obUser->getFio();
            foreach ($arDates as $date)
            {
                if(isset($arResultCountTasks[$date],$arResultCountTasks[$date][$obUser->id]))
                    $arResult[$key][] = $arResultCountTasks[$date][$obUser->id];
                else
                    $arResult[$key][] = 0;
            }
            foreach ($arDates as $date)
            {
                if(isset($arTaskLog[$date],$arTaskLog[$date][$obUser->id])) {

                    $arResult[$key][] = CustomHelper::getFormatedTaskTime($arTaskLog[$date][$obUser->id]);
                    //$arResult[$key][] = $arTaskLog[$date][$obUser->id];
                }
                else
                    $arResult[$key][] = 0;
            }
        }

        $fp = fopen(\Yii::getAlias('@backend/runtime/buser_task_stat.csv'), 'w');
        foreach ($arResult as $fields) {
            fputcsv($fp, $fields,';');
        }
        fclose($fp);

        $a0 = 0;






    }
}