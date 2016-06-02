<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%act_implicit_payment}}".
 *
 * @property integer $id
 * @property integer $act_id
 * @property integer $payment_id
 * @property integer $service_id
 * @property string $amount
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property Services $service
 * @property Acts $act
 * @property Payments $payment
 */
class ActImplicitPayment extends AbstractActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%act_implicit_payment}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[
                'act_id', 'payment_id',
                'service_id', 'created_at',
                'updated_at'
            ], 'integer'],
            [['amount'], 'number'],
            [['service_id'], 'exist', 'skipOnError' => true, 'targetClass' => Services::className(), 'targetAttribute' => ['service_id' => 'id']],
            [['act_id'], 'exist', 'skipOnError' => true, 'targetClass' => Acts::className(), 'targetAttribute' => ['act_id' => 'id']],
            [['payment_id'], 'exist', 'skipOnError' => true, 'targetClass' => Payments::className(), 'targetAttribute' => ['payment_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/book', 'ID'),
            'act_id' => Yii::t('app/book', 'Act ID'),
            'payment_id' => Yii::t('app/book', 'Payment ID'),
            'service_id' => Yii::t('app/book', 'Service ID'),
            'amount' => Yii::t('app/book', 'Amount'),
            'created_at' => Yii::t('app/book', 'Created At'),
            'updated_at' => Yii::t('app/book', 'Updated At'),
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
    public function getAct()
    {
        return $this->hasOne(Acts::className(), ['id' => 'act_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPayment()
    {
        return $this->hasOne(Payments::className(), ['id' => 'payment_id']);
    }
}
