<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%bonus_scheme_service}}".
 *
 * @property integer $id
 * @property integer $scheme_id
 * @property integer $service_id
 * @property string $month_percent
 * @property string $cost
 * @property integer $unit_multiple
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property Services $service
 * @property BonusScheme $scheme
 */
class BonusSchemeService extends AbstractActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%bonus_scheme_service}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['scheme_id', 'service_id', 'unit_multiple', 'created_at', 'updated_at'], 'integer'],
            [['month_percent'], 'string'],
            [['cost'], 'number']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/users', 'ID'),
            'scheme_id' => Yii::t('app/users', 'Scheme ID'),
            'service_id' => Yii::t('app/users', 'Service ID'),
            'month_percent' => Yii::t('app/users', 'Month Percent'),
            'cost' => Yii::t('app/users', 'Cost'),
            'unit_multiple' => Yii::t('app/users', 'Unit Multiple'),
            'created_at' => Yii::t('app/users', 'Created At'),
            'updated_at' => Yii::t('app/users', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getService()
    {
        return $this->hasOne(Services::className(), ['id' => 'service_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getScheme()
    {
        return $this->hasOne(BonusScheme::className(), ['id' => 'scheme_id']);
    }
}
