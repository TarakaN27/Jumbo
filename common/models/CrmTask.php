<?php

namespace common\models;

use common\components\helpers\CustomHelper;
use Yii;
use backend\models\BUser;
/**
 * This is the model class for table "{{%crm_task}}".
 *
 * @property integer $id
 * @property string $title
 * @property string $description
 * @property string $deadline
 * @property integer $priority
 * @property integer $type
 * @property integer $task_control
 * @property integer $parent_id
 * @property integer $assigned_id
 * @property integer $created_by
 * @property integer $time_estimate
 * @property integer $status
 * @property integer $date_start
 * @property integer $duration_fact
 * @property integer $closed_by
 * @property integer $closed_date
 * @property integer $cmp_id
 * @property integer $contact_id
 * @property integer $dialog_id
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property Dialogs $dialog
 * @property BUser $assigned
 * @property BUser $closedBy
 * @property CUser $cmp
 * @property CrmCmpContacts $contact
 * @property BUser $createdBy
 * @property CrmTask $parent
 * @property CrmTask[] $crmTasks
 * @property CrmTaskAccomplices[] $crmTaskAccomplices
 * @property BUser[] $busers
 * @property CrmTaskLogTime[] $crmTaskLogTimes
 * @property CrmTaskWatcher[] $crmTaskWatchers
 * @property BUser[] $busers0
 */
class CrmTask extends AbstractActiveRecord
{
    //статусы задачи
    CONST
        STATUS_OPENED = 1,
        STATUS_IN_PROGRESS = 2,
        STATUS_DONE = 3,
        STATUS_CLOSE  = 4;

    //приоритет задач
    CONST
        PRIORITY_HIGH = 3,
        PRIORITY_MIDDLE = 2,
        PRIORITY_LOW = 1;

    //тип задачи
    CONST
        TYPE_TASK = 1,
        TYPE_MEETING = 2,
        TYPE_CALL = 3,
        TYPE_OTHER =4;

    public
        $hourEstimate = '',
        $minutesEstimate = '';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%crm_task}}';
    }

    /**
     * @return array
     */
    public static function getTypeArr()
    {
        return [
            self::TYPE_TASK => Yii::t('app/crm','Task'),
            self::TYPE_CALL => Yii::t('app/crm','Call'),
            self::TYPE_MEETING => Yii::t('app/crm','Meeting'),
            self::TYPE_OTHER => Yii::t('app/crm','Other')
        ];
    }

    /**
     * @return string
     */
    public function getTypeStr()
    {
        $tmp = self::getTypeArr();
        return isset($tmp[$this->type]) ? $tmp[$this->type] : 'N/A';
    }

    /**
     * @return array
     */
    public static function getStatusArr()
    {
        return [
            self::STATUS_OPENED => Yii::t('app/crm','Status open'),
            self::STATUS_IN_PROGRESS => Yii::t('app/crm','Status in progress'),
            self::STATUS_DONE => Yii::t('app/crm','Status done'),
            self::STATUS_CLOSE => Yii::t('app/crm','Status close')
        ];
    }

    /**
     * @return array
     */
    public static function getStatusColorClassArr()
    {
        return [
            self::STATUS_OPENED => 'open_class',
            self::STATUS_IN_PROGRESS => 'in_progress_class',
            self::STATUS_DONE => 'done_class',
            self::STATUS_CLOSE => Yii::t('app/crm','close_class')
        ];
    }

    /**
     * @return bool
     */
    public function getStatusColorClass()
    {
        $tmp = self::getStatusColorClassArr();
        return isset($tmp[$this->status]) ? $tmp[$this->status] : '';
    }

    /**
     * @return string
     */
    public function getStatusStr()
    {
        $tmp = self::getStatusArr();
        return isset($tmp[$this->status]) ? $tmp[$this->status] : 'N/A';
    }

    /**
     * @return array
     */
    public static function getPriorityArr()
    {
        return [
            self::PRIORITY_HIGH => Yii::t('app/crm','Priority high'),
            self::PRIORITY_MIDDLE => Yii::t('app/crm','Priority middle'),
            self::PRIORITY_LOW => Yii::t('app/crm','Priority low')
        ];
    }

    /**
     * @return string
     */
    public function getPriorityStr()
    {
        $tmp = self::getPriorityArr();
        return isset($tmp[$this->priority]) ? $tmp[$this->priority] : 'N/A';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'assigned_id', 'created_by'], 'required'],
            [['description'], 'string'],
            [['deadline'], 'safe'],
            [[
                'priority', 'type', 'task_control',
                'parent_id', 'assigned_id', 'created_by',
                'time_estimate', 'status', 'date_start',
                'duration_fact', 'closed_by', 'closed_date',
                'cmp_id', 'contact_id', 'dialog_id',
                'created_at', 'updated_at','hourEstimate',
                'minutesEstimate'
            ], 'integer'],
            ['minutesEstimate','integer','min' => 1,'max' => 60],

            [['title'], 'string', 'max' => 255],
            ['status','default','value'=>self::STATUS_OPENED],

        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/crm', 'ID'),
            'title' => Yii::t('app/crm', 'Title'),
            'description' => Yii::t('app/crm', 'Description'),
            'deadline' => Yii::t('app/crm', 'Deadline'),
            'priority' => Yii::t('app/crm', 'Priority'),
            'type' => Yii::t('app/crm', 'Type'),
            'task_control' => Yii::t('app/crm', 'Task Control'),
            'parent_id' => Yii::t('app/crm', 'Parent ID'),
            'assigned_id' => Yii::t('app/crm', 'Assigned ID'),
            'created_by' => Yii::t('app/crm', 'Created By'),
            'time_estimate' => Yii::t('app/crm', 'Time Estimate'),
            'status' => Yii::t('app/crm', 'Status'),
            'date_start' => Yii::t('app/crm', 'Date Start'),
            'duration_fact' => Yii::t('app/crm', 'Duration Fact'),
            'closed_by' => Yii::t('app/crm', 'Closed By'),
            'closed_date' => Yii::t('app/crm', 'Closed Date'),
            'cmp_id' => Yii::t('app/crm', 'Cmp ID'),
            'contact_id' => Yii::t('app/crm', 'Contact ID'),
            'dialog_id' => Yii::t('app/crm', 'Dialog ID'),
            'created_at' => Yii::t('app/crm', 'Created At'),
            'updated_at' => Yii::t('app/crm', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDialog()
    {
        return $this->hasOne(Dialogs::className(), ['id' => 'dialog_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAssigned()
    {
        return $this->hasOne(BUser::className(), ['id' => 'assigned_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClosedBy()
    {
        return $this->hasOne(BUser::className(), ['id' => 'closed_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCmp()
    {
        return $this->hasOne(CUser::className(), ['id' => 'cmp_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContact()
    {
        return $this->hasOne(CrmCmpContacts::className(), ['id' => 'contact_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(BUser::className(), ['id' => 'created_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParent()
    {
        return $this->hasOne(CrmTask::className(), ['id' => 'parent_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCrmTasks()
    {
        return $this->hasMany(CrmTask::className(), ['parent_id' => 'id']);
    }

    /**
     * Получаем связь задача пользователь. Можем получить IDs пользователй, которые помогают
     * @return \yii\db\ActiveQuery
     */
    public function getCrmTaskAccomplices()
    {
        return $this->hasMany(CrmTaskAccomplices::className(), ['task_id' => 'id']);
    }

    /**
     * Получаем пользователей(объекты),которые помогают
     * @return \yii\db\ActiveQuery
     */
    public function getBusersAccomplices()
    {
        return $this->hasMany(BUser::className(), ['id' => 'buser_id'])->viaTable('{{%crm_task_accomplices}}', ['task_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCrmTaskLogTimes()
    {
        return $this->hasMany(CrmTaskLogTime::className(), ['task_id' => 'id']);
    }

    /**
     * Получаем связь задача пользователь. Можем получить IDS пользователей
     * @return \yii\db\ActiveQuery
     */
    public function getCrmTaskWatchers()
    {
        return $this->hasMany(CrmTaskWatcher::className(), ['task_id' => 'id']);
    }

    /**
     * Получаем пользователей(объекты) которые наблюдают
     * @return \yii\db\ActiveQuery
     */
    public function getBusersWatchers()
    {
        return $this->hasMany(BUser::className(), ['id' => 'buser_id'])->viaTable('{{%crm_task_watcher}}', ['task_id' => 'id']);
    }

    /**
     *
     */
    public function afterFind()
    {
        if(!empty($this->time_estimate))
        {
            $this->hourEstimate = sprintf('%02d', $this->time_estimate/3600);
            $this->minutesEstimate = sprintf('%02d', ($this->time_estimate % 3600)/60);
        }

        return parent::afterFind();
    }

    /**
     *
     */
    public function beforeSave($insert)
    {
        if(!empty($this->hourEstimate) || !empty($this->minutesEstimate))
            $this->time_estimate = (int)$this->minutesEstimate*60 + (int)$this->hourEstimate*3600;
        return parent::beforeSave($insert);
    }

    /**
     * @return string
     */
    public function getFormatedTimeEstimate()
    {
        return CustomHelper::getFormatedTaskTime($this->time_estimate);
    }
}
