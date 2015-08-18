<?php

namespace app\models;

use common\models\AbstractActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%units_cost_history}}".
 *
 * @property integer $id
 * @property integer $unit_id
 * @property string $date
 * @property integer $old_cost
 * @property integer $new_cost
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property Units $unit
 */
class UnitsCostHistory extends AbstractActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%units_cost_history}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['unit_id', 'date', 'old_cost', 'new_cost'], 'required'],
            [['unit_id', 'old_cost', 'new_cost', 'created_at', 'updated_at'], 'integer'],
            [['date'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/units', 'ID'),
            'unit_id' => Yii::t('app/units', 'Unit ID'),
            'date' => Yii::t('app/units', 'Date'),
            'old_cost' => Yii::t('app/units', 'Old Cost'),
            'new_cost' => Yii::t('app/units', 'New Cost'),
            'created_at' => Yii::t('app/units', 'Created At'),
            'updated_at' => Yii::t('app/units', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUnit()
    {
        return $this->hasOne(Units::className(), ['id' => 'unit_id']);
    }
}
