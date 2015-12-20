<?php

namespace common\models;

use Yii;
use backend\models\BUser;
/**
 * This is the model class for table "{{%crm_task_log_time}}".
 *
 * @property integer $id
 * @property integer $task_id
 * @property integer $buser_id
 * @property integer $spend_time
 * @property string  $description
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property BUser $buser
 * @property CrmTask $task
 */
class CrmTaskLogTime extends AbstractActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%crm_task_log_time}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['task_id','buser_id'],'required'],
            [['task_id', 'buser_id', 'spend_time', 'created_at', 'updated_at'], 'integer'],
            [['description'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/crm', 'ID'),
            'task_id' => Yii::t('app/crm', 'Task ID'),
            'buser_id' => Yii::t('app/crm', 'Buser ID'),
            'spend_time' => Yii::t('app/crm', 'Spend Time'),
            'description' => Yii::t('app/crm', 'Description'),
            'created_at' => Yii::t('app/crm', 'Created At'),
            'updated_at' => Yii::t('app/crm', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBuser()
    {
        return $this->hasOne(BUser::className(), ['id' => 'buser_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTask()
    {
        return $this->hasOne(CrmTask::className(), ['id' => 'task_id']);
    }
}
