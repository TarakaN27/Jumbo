<?php

namespace common\models;

use common\components\validators\WorkDayEndValidators;
use Yii;
use backend\models\BUser;

/**
 * This is the model class for table "{{%work_day}}".
 *
 * @property integer $id
 * @property integer $buser_id
 * @property string $log_date
 * @property integer $spent_time
 * @property integer $begin_time
 * @property integer $end_time
 * @property string $description
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property BUser $buser
 */
class WorkDay extends AbstractActiveRecord
{

    CONST
        SCENARIO_SAVE_END_TIME = 'save_end_time';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%work_day}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['buser_id'], 'required'],
            [['buser_id', 'spent_time', 'created_at', 'updated_at'], 'integer'],
            [['log_date','begin_time','end_time'], 'safe'],
            [['description'], 'string'],
            ['end_time','validateEndTime','on' => self::SCENARIO_SAVE_END_TIME]
           // ['begin_time','date','format' => 'php:y-m-d h:i','except' => 'insertLine']
        ];
    }

    /**
     * @param $attribute
     * @param $param
     */
    public function validateEndTime($attribute,$param)
    {
        if($this->begin_time > $this->end_time)
            $this->addError($attribute,'End day time can not be more then begin day time');
    }


    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/crm', 'ID'),
            'buser_id' => Yii::t('app/crm', 'Buser ID'),
            'log_date' => Yii::t('app/crm', 'Log Date'),
            'spent_time' => Yii::t('app/crm', 'Spent Time'),
            'begin_time' => Yii::t('app/crm', 'Begin Time'),
            'end_time' => Yii::t('app/crm', 'End Time'),
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
     * @param $iUserID
     * @return mixed
     */
    public static function getBeginedDay($iUserID)
    {
        return self::find()
            ->where(['buser_id' => $iUserID])
            ->andWhere('log_date = :log_date OR (end_time = "" OR end_time IS NULL OR end_time = 0)')
            ->params([':log_date' => date('Y-m-d',time())])
            ->one();
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if(!is_numeric($this->begin_time))
        {
            $this->begin_time = strtotime($this->begin_time);
        }

        return parent::beforeSave($insert);
    }

    /**
     * @return int
     */
    public function getCurrentSpendTime()
    {
        $time = $this->begin_time > $this->updated_at ? $this->updated_at : $this->begin_time;

        return $this->spent_time + (int)(time()-(int)$time);
    }

    /**
     * @return int
     */
    public function setSpentTime()
    {
        return $this->spent_time+=(int)((int)$this->end_time - (int)$this->begin_time);
    }

}
