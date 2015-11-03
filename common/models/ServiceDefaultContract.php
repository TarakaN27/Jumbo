<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%service_default_contract}}".
 *
 * @property integer $id
 * @property integer $service_id
 * @property integer $lp_id
 * @property string $cont_number
 * @property string $cont_date
 *
 * @property LegalPerson $lp
 * @property Services $service
 */
class ServiceDefaultContract extends AbstractActiveRecordWTB
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%service_default_contract}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['service_id', 'lp_id'], 'required'],
            [['service_id', 'lp_id'], 'integer'],
            [['cont_date'], 'safe'],
            [['cont_number'], 'string', 'max' => 255]
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
            'lp_id' => Yii::t('app/services', 'Lp ID'),
            'cont_number' => Yii::t('app/services', 'Cont Number'),
            'cont_date' => Yii::t('app/services', 'Cont Date'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLp()
    {
        return $this->hasOne(LegalPerson::className(), ['id' => 'lp_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getService()
    {
        return $this->hasOne(Services::className(), ['id' => 'service_id']);
    }
}
