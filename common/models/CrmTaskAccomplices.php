<?php

namespace common\models;

use common\components\behavior\notifications\TaskAccoplicesNotificationBehavior;
use Yii;
use backend\models\BUser;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%crm_task_accomplices}}".
 *
 * @property integer $task_id
 * @property integer $buser_id
 *
 * @property CrmTask $task
 * @property BUser $buser
 */
class CrmTaskAccomplices extends AbstractActiveRecordWTB
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%crm_task_accomplices}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['task_id', 'buser_id'], 'required'],
            [['task_id', 'buser_id'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'task_id' => Yii::t('app/crm', 'Task ID'),
            'buser_id' => Yii::t('app/crm', 'Buser ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTask()
    {
        return $this->hasOne(CrmTask::className(), ['id' => 'task_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBuser()
    {
        return $this->hasOne(BUser::className(), ['id' => 'buser_id']);
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        $arParent = parent::behaviors();
        return ArrayHelper::merge($arParent,[
            TaskAccoplicesNotificationBehavior::className() //уведомления
        ]);
    }
}
