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
 * @property integer $week
 * @property integer $monthly_type
 * @property integer $monthly_days
 * @property integer $counter_repeat
 *
 * @property CrmTask $task
 */
class CrmTaskRepeat extends AbstractActiveRecord
{
    CONST
        TYPE_DAILY = 1,
        TYPE_WEEKLY = 2,
        TYPE_MONTHLY = 3;

    CONST
        MONTHLY_TYPE_ONE =1,
        MONTHLY_TYPE_TWO = 2;

    CONST
        MONDAY = 1,
        TUESDAY = 2,
        WEDNESDAY = 3,
        THURSDAY = 4,
        FRIDAY = 5,
        SATURDAY = 6,
        SUNDAY = 7;

    CONST
        NUMBER_ITEM_FIRST = 1,
        NUMBER_ITEM_SECOND = 2,
        NUMBER_ITEM_THIRD = 3,
        NUMBER_ITEM_FOURTH = 4;

    CONST
        END_TYPE_INFINITE = 1,
        END_TYPE_COUNT_OCC = 2,
        END_TYPE_DATE = 3;

    public
        $useRepeatTask = false;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%crm_task_repeat}}';
    }

    /**
     * @return array
     */
    public static function getTypeMap()
    {
        return [
            self::TYPE_DAILY => Yii::t('app/crm','Daily'),
            self::TYPE_WEEKLY => Yii::t('app/crm','Weekly'),
            self::TYPE_MONTHLY => Yii::t('app/crm','Monthly')
        ];
    }

    /**
     * @return array
     */
    public static function getMonthlyDays()
    {
        return [
            self::MONDAY => Yii::t('app/crm','Monday'),
            self::TUESDAY => Yii::t('app/crm','Tuesday'),
            self::WEDNESDAY => Yii::t('app/crm','Wednesday'),
            self::THURSDAY => Yii::t('app/crm','Thursday'),
            self::FRIDAY => Yii::t('app/crm','Friday'),
            self::SATURDAY => Yii::t('app/crm','Saturday'),
            self::SUNDAY => Yii::t('app/crm','Sunday')
        ];
    }

    /**
     * @return array
     */
    public static function getMonthlyTypeMap()
    {
        return [
            self::MONTHLY_TYPE_ONE => Yii::t('app/crm','Monthly type one'),
            self::MONTHLY_TYPE_TWO => Yii::t('app/crm','Monthly type two')
        ];
    }

    /**
     * @return array
     */
    public static function getNumberItemMap()
    {
        return [
            self::NUMBER_ITEM_FIRST => Yii::t('app/crm','NUMBER_ITEM_FIRST'),
            self::NUMBER_ITEM_SECOND => Yii::t('app/crm','NUMBER_ITEM_SECOND'),
            self::NUMBER_ITEM_THIRD => Yii::t('app/crm','NUMBER_ITEM_THIRD'),
            self::NUMBER_ITEM_FOURTH => Yii::t('app/crm','NUMBER_ITEM_FOURTH'),
        ];
    }

    /**
     * @return array
     */
    public static function getEndTypeMap()
    {
        return [
            self::END_TYPE_INFINITE => Yii::t('app/crm','End type infinite'),
            self::END_TYPE_COUNT_OCC => Yii::t('app/crm','End type counting occurrences'),
            self::END_TYPE_DATE => Yii::t('app/crm','End type date'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['start_date','type'],'required',
                'when' => function($model){
                    return $this->useRepeatTask;
                },
                'whenClient' => "function (attribute, value) {
                    var
                        useRepeat = $('input[name = \"CrmTask[repeat_task]\"]:checked').val();
                    return useRepeat == 1;    
                }"
            ],
            [[
                'task_id', 'type', 'everyday',
                'everyday_custom', 'everyday_value', 'day',
                'month', 'monday', 'tuesday',
                'wednesday', 'thursday', 'friday',
                'saturday', 'sunday', 'number_of_item',
                'end_type', 'count_occurrences',
                'end_date', 'created_at', 'updated_at',
                'week','monthly_type','monthly_days','counter_repeat'
            ], 'integer'],
            [['task_id'], 'exist', 'skipOnError' => true, 'targetClass' => CrmTask::className(), 'targetAttribute' => ['task_id' => 'id']],
            ['start_date','safe'],
            ['day','integer','max' => 31,'min' => 1],
            [['day','month'],'required',
                'when' => function($model){
                    return $this->useRepeatTask && $this->type == self::TYPE_MONTHLY;
                },
                'whenClient' => "function (attribute, value) {
                    var
                        typeRepeat = $('input[name= \"CrmTaskRepeat[type]\"]:checked').val(),
                        useRepeat = $('input[name = \"CrmTask[repeat_task]\"]:checked').val();
                    return useRepeat == 1 && typeRepeat == 3;    
                }"
            ],
            [['week'],'required',
                'when' => function($model){
                    return $this->useRepeatTask && $this->type == self::TYPE_WEEKLY;
                },
                'whenClient' => "function (attribute, value) {
                    var
                        typeRepeat = $('input[name= \"CrmTaskRepeat[type]\"]:checked').val(),
                        useRepeat = $('input[name = \"CrmTask[repeat_task]\"]:checked').val();
                    return useRepeat == 1 && typeRepeat == 2;    
                }"
            ]
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
            'week' => Yii::t('app/crm', 'Week'),
            'monthly_type' => Yii::t('app/crm', 'Monthly type'),
            'monthly_days' => Yii::t('app/crm', 'Monthly days'),
            'counter_repeat' => Yii::t('app/crm','Repeat counter'),
        ];
    }

    /**
     * @return mixed|null
     */
    public function getTypeStr()
    {
        $arTmp = self::getTypeMap();
        return array_key_exists($this->type,$arTmp) ? $arTmp[$this->type] : NULL;
    }

    /**
     * @return mixed|null
     */
    public function getMonthlyTypeStr()
    {
        $arTmp = self::getMonthlyTypeMap();
        return array_key_exists($this->monthly_type,$arTmp) ? $arTmp[$this->monthly_type] : NULL;
    }

    /**
     * @return mixed|null
     */
    public function getMonthlyDaysStr()
    {
        $arTmp = self::getMonthlyDays();
        return array_key_exists($this->monthly_days,$arTmp) ? $arTmp[$this->monthly_days] : NULL;
    }

    /**
     * @return mixed|null
     */
    public function getNumberItemStr()
    {
        $arTmp = self::getNumberItemMap();
        return array_key_exists($this->number_of_item,$arTmp) ? $arTmp[$this->number_of_item] : NULL;
    }

    /**
     * @return mixed|null
     */
    public function getEndTypeStr()
    {
        $arTmp = self::getEndTypeMap();
        return array_key_exists($this->end_type,$arTmp) ? $arTmp[$this->end_type] : NULL;
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

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if(!is_numeric($this->start_date))
            $this->start_date = strtotime($this->start_date);

        return parent::beforeSave($insert);
    }

    /**
     * Get choosen week day
     * @return int|null
     */
    public function getWeekDay()
    {
        if($this->monday)
            return 1;
        elseif($this->tuesday)
            return 2;
        elseif ($this->wednesday)
            return 3;
        elseif ($this->thursday)
            return 4;
        elseif ($this->friday)
            return 5;
        elseif ($this->saturday)
            return 6;
        elseif ($this->sunday)
            return 0;
        else
            return NULL;
    }

    /**
     * @return bool
     */
    public function updateCounter()
    {
        return $this->updateCounters(['counter_repeat' => 1]);
    }

    /**
     * 
     */
    public function initForCreate()
    {
        $this->start_date = Yii::$app->formatter->asDate('NOW');
        $this->end_type = CrmTaskRepeat::END_TYPE_INFINITE;
        $this->type = CrmTaskRepeat::TYPE_DAILY;
        $this->monthly_type = CrmTaskRepeat::MONTHLY_TYPE_ONE;
    }
}
