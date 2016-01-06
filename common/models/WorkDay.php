<?php

namespace common\models;

use Yii;

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
           // ['begin_time','date','format' => 'php:y-m-d h:i','except' => 'insertLine']
        ];
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
            //->where(['buser_id' => $iUserID,'log_date' => date('Y-m-d',time())])
            ->where('log_date = :log_date OR (end_time = "" OR end_time IS NULL OR end_time = 0)')
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
        return $this->spent_time + (int)(time()-(int)$this->begin_time);
    }

    /**
     * @return int
     */
    public function setSpentTime()
    {
        return $this->spent_time+=(int)((int)$this->end_time - (int)$this->begin_time);
    }

}
