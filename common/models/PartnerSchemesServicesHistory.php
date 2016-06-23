<?php

namespace common\models;

use Yii;
use yii\helpers\Json;

/**
 * This is the model class for table "{{%partner_schemes_services_history}}".
 *
 * @property integer $id
 * @property integer $scheme_id
 * @property integer $service_id
 * @property string $ranges
 * @property string $legal
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $group_id
 *
 * @property Services $service
 * @property PartnerSchemes $scheme
 */
class PartnerSchemesServicesHistory extends AbstractActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%partner_schemes_services_history}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['scheme_id', 'service_id'], 'required'],
            [['scheme_id', 'service_id', 'created_at', 'updated_at','group_id'], 'integer'],
            [['ranges', 'legal'], 'string'],
            [['service_id'], 'exist', 'skipOnError' => true, 'targetClass' => Services::className(), 'targetAttribute' => ['service_id' => 'id']],
            [['scheme_id'], 'exist', 'skipOnError' => true, 'targetClass' => PartnerSchemes::className(), 'targetAttribute' => ['scheme_id' => 'id']],
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
            'ranges' => Yii::t('app/users', 'Ranges'),
            'legal' => Yii::t('app/users', 'Legal'),
            'created_at' => Yii::t('app/users', 'Created At'),
            'updated_at' => Yii::t('app/users', 'Updated At'),
            'group_id' => Yii::t('app/users','Group id')
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
        return $this->hasOne(PartnerSchemes::className(), ['id' => 'scheme_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGroup()
    {
        return $this->hasOne(PartnerSchemesServicesGroup::className(),['id' => 'group_id']);
    }

    /**
     *
     */
    public function afterFind()
    {
        if(!is_array($this->ranges) && !empty($this->ranges))
            $this->ranges = Json::decode($this->ranges);

        if(!is_array($this->legal) && !empty($this->legal))
            $this->legal = Json::decode($this->legal);

        return parent::afterFind();
    }
}
