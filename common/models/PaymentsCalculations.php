<?php

namespace common\models;

use common\components\loggingUserBehavior\LogModelBehavior;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%payments_calculations}}".
 *
 * @property integer $id
 * @property integer $payment_id
 * @property integer $pay_cond_id
 * @property string $tax
 * @property string $profit
 * @property string $production
 * @property string $cnd_corr_factor
 * @property string $cnd_commission
 * @property string $cnd_sale
 * @property string $cnd_tax
 * @property string $custom_curr
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property PaymentCondition $payCond
 * @property Payments $payment
 */
class PaymentsCalculations extends AbstractActiveRecord
{
    /**
     * @inheritdoc
     */
    const COEF_FOR_PROFIT_MANAGER = 0.03;
    public static function tableName()
    {
        return '{{%payments_calculations}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['payment_id', 'pay_cond_id', 'created_at', 'updated_at'], 'integer'],
            [['tax', 'production', 'custom_curr'], 'required'],
            [['tax', 'profit', 'production', 'cnd_corr_factor', 'cnd_commission', 'cnd_sale', 'cnd_tax'], 'number'],
            [['payment_id'],'unique','targetClass' => self::className(),
             'message' => Yii::t('app/book','This payment already calculated.')]
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
            'pay_cond_id' => Yii::t('app/book', 'Pay Cond ID'),
            'tax' => Yii::t('app/book', 'Tax value'),
            'profit' => Yii::t('app/book', 'Profit'),
            'production' => Yii::t('app/book', 'Production'),
            'cnd_corr_factor' => Yii::t('app/book', 'Cnd Corr Factor'),
            'cnd_commission' => Yii::t('app/book', 'Cnd Commission'),
            'cnd_sale' => Yii::t('app/book', 'Cnd Sale'),
            'cnd_tax' => Yii::t('app/book', 'Cnd Tax'),
            'created_at' => Yii::t('app/book', 'Created At'),
            'updated_at' => Yii::t('app/book', 'Updated At'),
            'profit_for_manager' =>Yii::t('app/reports', 'Profit for manager'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPayCond()
    {
        return $this->hasOne(PaymentCondition::className(), ['id' => 'pay_cond_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPayment()
    {
        return $this->hasOne(Payments::className(), ['id' => 'payment_id']);
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        $arBhvrs = parent::behaviors();
        return ArrayHelper::merge(
            $arBhvrs,
            [
                [
                    'class' => LogModelBehavior::className(),
                    'ignored' => ['created_at','updated_at']
                ]
            ]);
    }
}
