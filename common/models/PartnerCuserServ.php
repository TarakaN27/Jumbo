<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%partner_cuser_serv}}".
 *
 * @property integer $id
 * @property integer $partner_id
 * @property integer $cuser_id
 * @property integer $service_id
 * @property string $connect
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $archive
 *
 * @property CUser $cuser
 * @property CUser $partner
 * @property Services $service
 */
class PartnerCuserServ extends AbstractActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%partner_cuser_serv}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['partner_id', 'cuser_id', 'service_id'], 'required'],
            [['partner_id', 'cuser_id', 'service_id', 'created_at', 'updated_at', 'archive'], 'integer'],
            [['connect'], 'safe'],
            [['cuser_id'], 'exist', 'skipOnError' => true, 'targetClass' => CUser::className(), 'targetAttribute' => ['cuser_id' => 'id']],
            [['partner_id'], 'exist', 'skipOnError' => true, 'targetClass' => CUser::className(), 'targetAttribute' => ['partner_id' => 'id']],
            [['service_id'], 'exist', 'skipOnError' => true, 'targetClass' => Services::className(), 'targetAttribute' => ['service_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/users', 'ID'),
            'partner_id' => Yii::t('app/users', 'Partner ID'),
            'cuser_id' => Yii::t('app/users', 'Cuser ID'),
            'service_id' => Yii::t('app/users', 'Service ID'),
            'connect' => Yii::t('app/users', 'Connect'),
            'created_at' => Yii::t('app/users', 'Created At'),
            'updated_at' => Yii::t('app/users', 'Updated At'),
            'archive' => Yii::t('app/users', 'Archive'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCuser()
    {
        return $this->hasOne(CUser::className(), ['id' => 'cuser_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPartner()
    {
        return $this->hasOne(CUser::className(), ['id' => 'partner_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getService()
    {
        return $this->hasOne(Services::className(), ['id' => 'service_id']);
    }
}
