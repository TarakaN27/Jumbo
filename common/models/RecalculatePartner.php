<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%recalculate_partner}}".
 *
 * @property integer $id
 * @property integer $cuser_id
 * @property string $begin_date
 * @property integer $payment_id
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $service_id
 *
 * @property Payments $payment
 * @property CUser $cuser
 */
class RecalculatePartner extends AbstractActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%recalculate_partner}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['cuser_id'], 'required'],
            [['cuser_id', 'payment_id', 'created_at', 'updated_at','service_id'], 'integer'],
            [['begin_date'], 'safe'],
            [['payment_id'], 'exist', 'skipOnError' => true, 'targetClass' => Payments::className(), 'targetAttribute' => ['payment_id' => 'id']],
            [['cuser_id'], 'exist', 'skipOnError' => true, 'targetClass' => CUser::className(), 'targetAttribute' => ['cuser_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/users', 'ID'),
            'cuser_id' => Yii::t('app/users', 'Cuser ID'),
            'begin_date' => Yii::t('app/users', 'Begin Date'),
            'payment_id' => Yii::t('app/users', 'Payment ID'),
            'created_at' => Yii::t('app/users', 'Created At'),
            'updated_at' => Yii::t('app/users', 'Updated At'),
            'service_id' => Yii::t('app/users','Service ID')
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPayment()
    {
        return $this->hasOne(Payments::className(), ['id' => 'payment_id']);
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
        return $this->hasOne(Services::className(),['id' => 'service_id']);
    }
}
