<?php

namespace common\models;

use common\components\behavior\Task\TaskQuantityHoursBehavior;
use common\components\helpers\CustomHelper;
use Yii;
use backend\models\BUser;
use yii\helpers\ArrayHelper;

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
 * @property integer $log_date
 *
 * @property BUser $buser
 * @property CrmTask $task
 */
class CrmTaskLogTime extends AbstractActiveRecord
{
    public
        $hour,
        $minutes;

    CONST
        SCENARIO_UPDATE = 'log_update',
        SCENARIO_LOG_TIME = 'log_time';

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
            [['task_id', 'buser_id', 'spend_time', 'created_at', 'updated_at','hour','minutes'], 'integer'],
            [['description','log_date'], 'string'],
            ['log_date', 'date', 'format' => 'php:Y-m-d'],
            [['hour','minutes'],'integer','min' => 0],
            ['hour','integer','max' => 24],
            ['minutes','integer','max' => 60],
            [['hour','minutes'],'required','on' => [self::SCENARIO_LOG_TIME,self::SCENARIO_UPDATE]]
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
            'log_date' => Yii::t('app/crm','Log date'),
            'hour' => Yii::t('app/crm','Hour'),
            'minutes' => Yii::t('app/crm','Minutes'),
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

    /**
     * @return string
     */
    public function getFormatedSpendTime()
    {
        return CustomHelper::getFormatedTaskTime($this->spend_time);
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if($this->scenario == self::SCENARIO_LOG_TIME)
        {
            $this->spend_time = (int)$this->spend_time + (int)($this->hour*3600) + (int)($this->minutes*60);
        }

        if($this->scenario == self::SCENARIO_UPDATE)
        {
            $this->spend_time = (int)($this->hour*3600) + (int)($this->minutes*60);
        }

        return parent::beforeSave($insert);
    }

    /**
     * @return bool
     */
    public function covertSecondsToTime()
    {
        $this->hour = (int)($this->spend_time/3600);
        $this->minutes = (int)(($this->spend_time % 3600)/60) ;
        return FALSE;
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        $tmp = parent::behaviors();
        return ArrayHelper::merge($tmp,[
            TaskQuantityHoursBehavior::className()      //норма часы
        ]);
    }
}
