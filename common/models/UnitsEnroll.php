<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%units_enroll}}".
 *
 * @property integer $id
 * @property string $name
 *
 * @property PaymentCondition[] $paymentConditions
 */
class UnitsEnroll extends AbstractActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%units_enroll}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => Yii::t('app/services', 'ATTR_NAME')
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPaymentConditions()
    {
        return $this->hasMany(PaymentCondition::className(), ['enroll_unit_id' => 'id']);
    }
    public static function getUnitsEnrollsDropDown(){
        return ArrayHelper::map(static::find()->all(), 'id','name');
        
    }
}
