<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%promised_pay_repay}}".
 *
 * @property integer $id
 * @property string $amount
 * @property integer $pr_pay_id
 * @property integer $payment_id
 * @property integer $enroll_id
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property Enrolls $enroll
 * @property Payments $payment
 * @property PromisedPayment $prPay
 */
class PromisedPayRepay extends AbstractActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%promised_pay_repay}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['amount'], 'number'],
            [['pr_pay_id', 'payment_id', 'enroll_id', 'created_at', 'updated_at'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/book', 'ID'),
            'amount' => Yii::t('app/book', 'Amount'),
            'pr_pay_id' => Yii::t('app/book', 'Pr Pay ID'),
            'payment_id' => Yii::t('app/book', 'Payment ID'),
            'enroll_id' => Yii::t('app/book', 'Enroll ID'),
            'created_at' => Yii::t('app/book', 'Created At'),
            'updated_at' => Yii::t('app/book', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEnroll()
    {
        return $this->hasOne(Enrolls::className(), ['id' => 'enroll_id']);
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
    public function getPrPay()
    {
        return $this->hasOne(PromisedPayment::className(), ['id' => 'pr_pay_id']);
    }
}
