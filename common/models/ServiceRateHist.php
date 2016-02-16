<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%service_rate_hist}}".
 *
 * @property integer $id
 * @property integer $service_id
 * @property string $date
 * @property double $old_rate
 * @property double $new_rate
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property Services $service
 */
class ServiceRateHist extends AbstractActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%service_rate_hist}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['service_id', 'created_at', 'updated_at'], 'integer'],
            [['date'], 'safe'],
            [['old_rate', 'new_rate'], 'number']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/services', 'ID'),
            'service_id' => Yii::t('app/services', 'Service ID'),
            'date' => Yii::t('app/services', 'Date'),
            'old_rate' => Yii::t('app/services', 'Old Rate'),
            'new_rate' => Yii::t('app/services', 'New Rate'),
            'created_at' => Yii::t('app/services', 'Created At'),
            'updated_at' => Yii::t('app/services', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getService()
    {
        return $this->hasOne(Services::className(), ['id' => 'service_id']);
    }
}
