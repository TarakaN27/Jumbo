<?php

namespace common\models;

use Yii;
use backend\models\BUser;
/**
 * This is the model class for table "{{%calendar_days}}".
 *
 * @property integer $id
 * @property integer $buser_id
 * @property string $date
 * @property integer $type
 * @property integer $work_hour
 * @property string $description
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property BUser $buser
 */
class CalendarDays extends AbstractActiveRecord
{
    CONST
        TYPE_WORK_DAY = 5,
        TYPE_HOLIDAY = 10;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%calendar_days}}';
    }

    /**
     * @return array
     */
    public static function getTypeArr()
    {
        return [
            self::TYPE_WORK_DAY => Yii::t('app/reports','Work day'),
            self::TYPE_HOLIDAY => Yii::t('app/reports','Holiday')
        ];
    }

    /**
     * @return null
     */
    public function getTypeStr()
    {
        $tmp = self::getTypeArr();
        return isset($tmp[$this->type]) ? $tmp[$this->type] : NULL;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['buser_id', 'type', 'work_hour', 'created_at', 'updated_at'], 'integer'],
            [['date'], 'safe'],
            ['date','date','format'=>'Y-m-d'],
            [['description'], 'string'],
            ['work_hour','integer','min'=>0,'max'=>24],
            ['type','in', 'range' => array_keys(self::getTypeArr())],
            ['date','unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/reports', 'ID'),
            'buser_id' => Yii::t('app/reports', 'Buser ID'),
            'date' => Yii::t('app/reports', 'Date'),
            'type' => Yii::t('app/reports', 'Type'),
            'work_hour' => Yii::t('app/reports', 'Work Hour'),
            'description' => Yii::t('app/reports', 'Description'),
            'created_at' => Yii::t('app/reports', 'Created At'),
            'updated_at' => Yii::t('app/reports', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBuser()
    {
        return $this->hasOne(BUser::className(), ['id' => 'buser_id']);
    }
}
