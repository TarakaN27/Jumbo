<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 14.6.16
 * Time: 10.41
 */

namespace common\components\infoHelpers;


use backend\models\BUser;
use common\models\CrmTask;
use yii\db\Query;

class ManagersInfo
{

    public function getActivityAfterCall($beginDate='2016-06-06 00:00',$endDate = '2016-06-10 23:59:59',$arUserIds = [50,47,44])
    {
        $arUser = BUser::find()->where(['id' => $arUserIds])->all();


        $arResult = [];
        /** @var $obUser $obUser */
        foreach ($arUser as $obUser)
        {
            $arTasks = CrmTask::find()
                ->where(['created_by' => $obUser->id])
                ->andWhere('created_at >= :beginDate AND created_at <= :endDate')
                ->andWhere([
                    'type' => CrmTask::TYPE_CALL,
                    'priority' => CrmTask::PRIORITY_LOW
                ])
                ->params([
                    ':beginDate' => strtotime($beginDate),
                    ':endDate' => strtotime($endDate)
                ])
                ->orderBy(['created_at' => SORT_ASC])
                ->all();

            $arResult[$obUser->getFio()]['callNum'] = count($arTasks);

            if($arTasks)
            {
                $arCmp = [];
                foreach ($arTasks as $tmp)
                {
                    if(isset($arCmp[$tmp->cmp_id]))
                    {
                        $arCmp[$tmp->cmp_id] = $arCmp[$tmp->cmp_id] > $tmp->created_at ? $tmp->created_at : $arCmp[$tmp->cmp_id];
                    }else{
                        $arCmp[$tmp->cmp_id] = $tmp->created_at;
                    }
                }
                $iCounter = 0;
                foreach ($arCmp as $cmpID => $bDate)
                {
                    $arTasksCmp = CrmTask::find()
                        ->where([
                            'cmp_id' => $cmpID,
                            'type' => CrmTask::TYPE_TASK
                        ])
                        ->andWhere('created_at >= :beginDate')
                        ->params([
                            ':beginDate' => $bDate,
                        ])
                        ->all();

                    $arResult[$obUser->getFio()]['taskCmp'][$cmpID] = count($arTasksCmp);
                    $iCounter+=count($arTasksCmp);
                }
                $arResult[$obUser->getFio()]['totalTasks'] = $iCounter;

            }else{
                $arResult[$obUser->getFio()]['taskCmp'] = NULL;
            }
        }


        var_dump($arResult);
    }

    public function getActivityOne()
    {
        $arTasks = CrmTask::find()
            ->where([
                'type' => CrmTask::TYPE_CALL
            ])
            ->andWhere(['like', 'title', 'первый'])
            ->andWhere('created_at >= :beginDate')
            ->params([
                'beginDate' => 1465171200
            ])
            ->orderBy(['created_at' => SORT_ASC])
            ->all();

            $arCmp = [];
            foreach ($arTasks as $tmp)
            {
                if(isset($arCmp[$tmp->cmp_id]))
                {
                    $arCmp[$tmp->cmp_id] = $arCmp[$tmp->cmp_id] > $tmp->created_at ? $tmp->created_at : $arCmp[$tmp->cmp_id];
                }else{
                    $arCmp[$tmp->cmp_id] = $tmp->created_at;
                }
            }
            $arCountCmp = [];
            foreach ($arCmp as $cmpID => $bDate)
            {
                $arTasksCmp = CrmTask::find()
                    ->where([
                        'cmp_id' => $cmpID,
                        'type' => CrmTask::TYPE_TASK
                    ])
                    ->andWhere('created_at >= :beginDate')
                    ->params([
                        ':beginDate' => $bDate,
                    ])
                    ->all();

                if(count($arTasksCmp) > 0 && !in_array($cmpID,$arCountCmp))
                    $arCountCmp[] = $cmpID;
            }


        var_dump(count($arCountCmp));
        die;



    }





}