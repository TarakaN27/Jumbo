<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 17.5.16
 * Time: 16.23
 */

namespace common\models\managers;


use common\models\CrmCmpFile;
use common\models\CrmTask;
use yii\base\Exception;
use yii\jui\Dialog;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

class CrmTaskManager extends CrmTask
{
    /**
     * @param $taskID
     * @param bool $setRecurringID
     * @return CrmTask|null
     * @throws NotFoundHttpException
     * @throws \yii\db\Exception
     */
    public function cloneTask($taskID,$setRecurringID = FALSE)
    {
        /** @var CrmTask $obTask */
        $obTask = CrmTask::find()->where(['id' => $taskID])->one();
        if(!$obTask)
            throw new NotFoundHttpException();

        $arBUsersWatchers = $obTask->busersWatchers;
        $arTaskFiles = $obTask->taskFiles;
        $arBUserAccomplices = $obTask->busersAccomplices;

        $transaction  = \Yii::$app->db->beginTransaction();

        try {
            //save new task
            $obTask->id = NULL;
            $obTask->isNewRecord = TRUE;
            $obTask->repeat_task = 0;
            $obTask->status = CrmTask::STATUS_OPENED;
            $obTask->closed_by = '';
            $obTask->closed_date = '';
            if($setRecurringID)
                $obTask->recurring_id = $taskID;
            if(!$obTask->save())
                throw new ServerErrorHttpException();

            $obTask->createDialogForTask($obTask->created_by);              //create dialog for task

            foreach ($arBUserAccomplices as $obBUA)
                $obTask->link('busersAccomplices',$obBUA);

            foreach ($arBUsersWatchers as $obBUW)
                $obTask->link('busersAccomplices',$obBUW);

            /** @var CrmCmpFile $file */
            foreach ($arTaskFiles as $file)
            {
                $file->id = null;
                $file->isNewRecord = TRUE;
                $file->task_id = $obTask->id;
                if(!$file->save())
                    throw new ServerErrorHttpException();
            }

            /** @var CrmTask $modelUpd */
            $modelUpd = CrmTask::find()->where(['id' => $obTask->id])->one();
            if (!$modelUpd)
                throw new NotFoundHttpException('Model not found');
            $modelUpd->callTriggerUpdateDialog();  //обновление пользователй причастных к диалогу

            $transaction->commit();
            return $modelUpd;
        }catch (Exception $e)
        {
            $transaction->rollBack();
            return NULL;
        }
    }

}