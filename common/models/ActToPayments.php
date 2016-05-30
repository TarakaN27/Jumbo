<?php

namespace common\models;

use common\components\helpers\CustomDateHelper;
use common\components\helpers\CustomHelper;
use Yii;

/**
 * This is the model class for table "{{%act_to_payments}}".
 *
 * @property integer $id
 * @property integer $act_id
 * @property integer $payment_id
 * @property string $amount
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property Payments $payment
 * @property Acts $act
 */
class ActToPayments extends AbstractActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%act_to_payments}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['act_id', 'payment_id'], 'required'],
            [['act_id', 'payment_id', 'created_at', 'updated_at'], 'integer'],
            [['amount'], 'number'],
            [['payment_id'], 'exist', 'skipOnError' => true, 'targetClass' => Payments::className(), 'targetAttribute' => ['payment_id' => 'id']],
            [['act_id'], 'exist', 'skipOnError' => true, 'targetClass' => Acts::className(), 'targetAttribute' => ['act_id' => 'id']],
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
            'amount' => Yii::t('app/book', 'Amount'),
            'created_at' => Yii::t('app/book', 'Created At'),
            'updated_at' => Yii::t('app/book', 'Updated At'),
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
    public function getAct()
    {
        return $this->hasOne(Acts::className(), ['id' => 'act_id']);
    }

    /**
     * @param array $arPaymentsIds
     * @return array
     */
    public static function getRecordsByPaymentsId(array $arPaymentsIds)
    {
        $arTmp = self::find()->where(['payment_id' => $arPaymentsIds])->all();
        return $arTmp ? CustomHelper::getMapArrayObjectByAttribute($arTmp,'payment_id') : [];
    }
}
