<?php

namespace common\models;

use common\components\behavior\notifications\EnrollmentRequestNotificationBehavior;
use Yii;
use backend\models\BUser;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%enrollment_request}}".
 *
 * @property integer $id
 * @property integer $payment_id
 * @property integer $pr_payment_id
 * @property integer $service_id
 * @property integer $assigned_id
 * @property integer $cuser_id
 * @property string $amount
 * @property string $pay_amount
 * @property integer $pay_currency
 * @property integer $pay_date
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $status
 *
 * @property CUser $cuser
 * @property BUser $assigned
 * @property Payments $payment
 * @property PromisedPayment $prPayment
 * @property Services $service
 */
class EnrollmentRequest extends AbstractActiveRecord
{
    CONST
        STATUS_NEW = 5,
        STATUS_PROCESSED = 10;

    /**
     * @return array
     */
    public static function getStatusArr()
    {
        return [
            self::STATUS_NEW => Yii::t('app/book','Status new'),
            self::STATUS_PROCESSED => Yii::t('app/book','Status processed')
        ];
    }

    /**
     * @return null|string
     */
    public function getStatusStr()
    {
        $tmp = self::getStatusArr();
        return isset($tmp[$this->status]) ? $tmp[$this->status] : NULL;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%enrollment_request}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[
                'payment_id',
                'pr_payment_id',
                'service_id',
                'assigned_id',
                'cuser_id',
                'pay_currency',
                'pay_date',
                'created_at',
                'updated_at',
                'status'
            ], 'integer'],
            [['service_id', 'cuser_id'], 'required'],
            [['amount', 'pay_amount'], 'number'],
            ['status','default', 'value' => self::STATUS_NEW]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/book', 'ID'),
            'payment_id' => Yii::t('app/book', 'Payment ID'),
            'pr_payment_id' => Yii::t('app/book', 'Pr Payment ID'),
            'service_id' => Yii::t('app/book', 'Service ID'),
            'assigned_id' => Yii::t('app/book', 'Assigned ID'),
            'cuser_id' => Yii::t('app/book', 'Cuser ID'),
            'amount' => Yii::t('app/book', 'Unit amount'),
            'pay_amount' => Yii::t('app/book', 'Pay Amount'),
            'pay_currency' => Yii::t('app/book', 'Pay Currency'),
            'pay_date' => Yii::t('app/book', 'Pay Date'),
            'created_at' => Yii::t('app/book', 'Created At'),
            'updated_at' => Yii::t('app/book', 'Updated At'),
            'status' => Yii::t('app/book','Status')
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
    public function getAssigned()
    {
        return $this->hasOne(BUser::className(), ['id' => 'assigned_id']);
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
    public function getPrPayment()
    {
        return $this->hasOne(PromisedPayment::className(), ['id' => 'pr_payment_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getService()
    {
        return $this->hasOne(Services::className(), ['id' => 'service_id']);
    }

    public function behaviors()
    {
        $arTmp = parent::behaviors();
        return ArrayHelper::merge($arTmp,[
            EnrollmentRequestNotificationBehavior::className()
        ]);
    }
}
