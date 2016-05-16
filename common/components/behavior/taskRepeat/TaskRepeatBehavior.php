<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 16.5.16
 * Time: 14.04
 */

namespace common\components\behavior\taskRepeat;


use common\models\AbstractActiveRecord;
use common\models\CrmTask;
use common\models\CrmTaskRepeat;
use yii\base\Behavior;

class TaskRepeatBehavior extends Behavior
{
    
    public function events()
    {
        return [
            CrmTaskRepeat::EVENT_AFTER_DELETE => 'afterDelete'
        ];
    }

    /**
     * @return bool
     */
    public function afterDelete()
    {
        /** @var CrmTaskRepeat $model */
        $model = $this->owner;
        if($model->task_id)
            CrmTask::updateAll(['repeat_task' => AbstractActiveRecord::NO],['id' => $model->task_id]);
        return TRUE;
    }

}