<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%cuser_service_contract}}".
 *
 * @property integer $id
 * @property integer $service_id
 * @property integer $cuser_id
 * @property string $cont_number
 * @property string $cont_date
 *
 * @property CUser $cuser
 * @property Services $service
 */
class CuserServiceContract extends AbstractActiveRecordWTB
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%cuser_service_contract}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['service_id', 'cuser_id'], 'required'],
            [['service_id', 'cuser_id'], 'integer'],
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
            'id' => Yii::t('app/users', 'ID'),
            'service_id' => Yii::t('app/users', 'Service ID'),
            'cuser_id' => Yii::t('app/users', 'Cuser ID'),
            'cont_number' => Yii::t('app/users', 'Cont Number'),
            'cont_date' => Yii::t('app/users', 'Cont Date'),
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
    public function getService()
    {
        return $this->hasOne(Services::className(), ['id' => 'service_id']);
    }
}
