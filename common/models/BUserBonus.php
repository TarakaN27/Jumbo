<?php

namespace common\models;

use Yii;
use backend\models\BUser;
/**
 * This is the model class for table "{{%b_user_bonus}}".
 *
 * @property integer $id
 * @property string $amount
 * @property integer $buser_id
 * @property integer $scheme_id
 * @property integer $payment_id
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $service_id
 * @property integer $cuser_id
 * @property integer $currency_id
 * @property integer $record_id
 *
 * @property Payments $payment
 * @property BUser $buser
 * @property BonusScheme $scheme
 */
class BUserBonus extends AbstractActiveRecord
{
    public $totalSum;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%b_user_bonus}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['amount'], 'number'],
            [[
                'buser_id', 'scheme_id', 'payment_id',
                'created_at', 'updated_at','cuser_id',
                'service_id','currency_id','record_id'
            ], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/bonus', 'ID'),
            'amount' => Yii::t('app/bonus', 'Bonus amount'),
            'buser_id' => Yii::t('app/bonus', 'Buser ID'),
            'scheme_id' => Yii::t('app/bonus', 'Scheme ID'),
            'payment_id' => Yii::t('app/bonus', 'Payment ID'),
            'created_at' => Yii::t('app/bonus', 'Created At'),
            'updated_at' => Yii::t('app/bonus', 'Updated At'),
            'cuser_id' => Yii::t('app/bonus', 'CUser ID'),
            'service_id' => Yii::t('app/bonus', 'Service ID'),
            'currency_id' => Yii::t('app/bonus','Bonus currency ID'),
            'record_id' => Yii::t('app/bonus','Record ID'),
            'bonus_percent' => Yii::t('app/bonus','Bonus Percent'),
            'number_month' => Yii::t('app/bonus','Number month'),
            'is_sale' => Yii::t('app/bonus','Is sale')
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
    public function getCalculation()
    {
        return $this->hasOne(PaymentsCalculations::className(), ['payment_id' => 'payment_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBuser()
    {
        return $this->hasOne(BUser::className(), ['id' => 'buser_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getScheme()
    {
        return $this->hasOne(BonusScheme::className(), ['id' => 'scheme_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getService()
    {
        return $this->hasOne(Services::className(),['id' => 'service_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCuser()
    {
        return $this->hasOne(CUser::className(),['id' => 'cuser_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCurrency()
    {
        return $this->hasOne(ExchangeRates::className(),['id' => 'currency_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPaymentRecord()
    {
        return $this->hasOne(BUserPaymentRecords::className(),['id' => 'record_id']);
    }
}
