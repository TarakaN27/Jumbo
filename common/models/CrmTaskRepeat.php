<?php

namespace common\models;

use common\components\behavior\taskRepeat\TaskRepeatBehavior;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%crm_task_repeat}}".
 *
 * @property integer $id
 * @property integer $task_id
 * @property integer $type
 * @property integer $everyday
 * @property integer $everyday_custom
 * @property integer $everyday_value
 * @property integer $day
 * @property integer $month
 * @property integer $monday
 * @property integer $tuesday
 * @property integer $wednesday
 * @property integer $thursday
 * @property integer $friday
 * @property integer $saturday
 * @property integer $sunday
 * @property integer $number_of_item
 * @property integer $start_date
 * @property integer $end_type
 * @property integer $count_occurrences
 * @property integer $end_date
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property CrmTask $task
 */
class CrmTaskRepeat extends AbstractActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%crm_task_repeat}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[
                'task_id', 'type', 'everyday',
                'everyday_custom', 'everyday_value', 'day',
                'month', 'monday', 'tuesday',
                'wednesday', 'thursday', 'friday',
                'saturday', 'sunday', 'number_of_item',
                'start_date', 'end_type', 'count_occurrences',
                'end_date', 'created_at', 'updated_at'
            ], 'integer'],
            [['task_id'], 'exist', 'skipOnError' => true, 'targetClass' => CrmTask::className(), 'targetAttribute' => ['task_id' => 'id']],
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
            'type' => Yii::t('app/crm', 'Type'),
            'everyday' => Yii::t('app/crm', 'Everyday'),
            'everyday_custom' => Yii::t('app/crm', 'Everyday Custom'),
            'everyday_value' => Yii::t('app/crm', 'Everyday Value'),
            'day' => Yii::t('app/crm', 'Day'),
            'month' => Yii::t('app/crm', 'Month'),
            'monday' => Yii::t('app/crm', 'Monday'),
            'tuesday' => Yii::t('app/crm', 'Tuesday'),
            'wednesday' => Yii::t('app/crm', 'Wednesday'),
            'thursday' => Yii::t('app/crm', 'Thursday'),
            'friday' => Yii::t('app/crm', 'Friday'),
            'saturday' => Yii::t('app/crm', 'Saturday'),
            'sunday' => Yii::t('app/crm', 'Sunday'),
            'number_of_item' => Yii::t('app/crm', 'Number Of Item'),
            'start_date' => Yii::t('app/crm', 'Start Date'),
            'end_type' => Yii::t('app/crm', 'End Type'),
            'count_occurrences' => Yii::t('app/crm', 'Count Occurrences'),
            'end_date' => Yii::t('app/crm', 'End Date'),
            'created_at' => Yii::t('app/crm', 'Created At'),
            'updated_at' => Yii::t('app/crm', 'Updated At'),
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
     * @return array
     */
    public function behaviors()
    {
        return ArrayHelper::merge(
            parent::behaviors(),
            [
                TaskRepeatBehavior::className()             //поведение модели
            ]);
    }
}
