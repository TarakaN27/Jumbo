<?php

namespace common\models;

use backend\models\BUser;
use Yii;

/**
 * This is the model class for table "{{%payment_request}}".
 *
 * @property integer $id
 * @property integer $cntr_id
 * @property integer $manager_id
 * @property integer $owner_id
 * @property integer $is_unknown
 * @property string $user_name
 * @property integer $pay_date
 * @property string $pay_summ
 * @property integer $currency_id
 * @property integer $legal_id
 * @property string $description
 * @property integer $dialog_id
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 */
class PaymentRequest extends AbstractActiveRecord
{
    CONST
        STATUS_NEW = 5,
        STATUS_IN_PROCESS = 10,
        STATUS_CANCELED = 15,
        STATUS_FINISHED = 20;

    /**
     * @return array
     */
    public static function getStatusArr()
    {
        return [
            self::STATUS_NEW => Yii::t('app/book', 'Status new'),
            self::STATUS_IN_PROCESS => Yii::t('app/book', 'Status in process'),
            self::STATUS_CANCELED => Yii::t('app/book', 'Status canceled'),
            self::STATUS_FINISHED => Yii::t('app/book', 'Status finished'),
        ];
    }

    /**
     * @return string
     */
    public function getStatusStr()
    {
        $tmp = self::getStatusArr();
        return array_key_exists($this->status,$tmp) ? $tmp[$this->status] : 'N/A';
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%payment_request}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['owner_id','pay_date', 'currency_id', 'legal_id'],'required'],
            [[
                 'cntr_id', 'manager_id', 'owner_id', 'is_unknown',
                  'currency_id', 'legal_id', 'dialog_id',
                 'status', 'created_at', 'updated_at'
             ], 'integer'],
            [['pay_date', 'pay_summ', 'currency_id', 'legal_id'], 'required'],
            [['pay_summ'], 'number'],
            [['description'], 'string'],
            [['user_name'], 'string', 'max' => 255],
            [['cntr_id','manager_id'],'required',
             'when' => function($model) {
                    return $model->is_unknown != self::YES;
                },
             'whenClient' => "function (attribute, value) {
                    return $('#paymentrequest-is_unknown').val() != '".self::YES."';
                }"
            ],
            [['user_name'],'required',
             'when' => function($model) {
                     return $model->is_unknown == self::YES;
                 },
              'whenClient' => "function (attribute, value) {
                     return $('#paymentrequest-is_unknown').val() == '".self::YES."';
                 }"
            ]

        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/book', 'ID'),
            'cntr_id' => Yii::t('app/book', 'Cntr ID'),
            'manager_id' => Yii::t('app/book', 'Manager ID'),
            'owner_id' => Yii::t('app/book', 'Owner ID'),
            'is_unknown' => Yii::t('app/book', 'Is Unknown'),
            'user_name' => Yii::t('app/book', 'User Name'),
            'pay_date' => Yii::t('app/book', 'Pay Date'),
            'pay_summ' => Yii::t('app/book', 'Pay Summ'),
            'currency_id' => Yii::t('app/book', 'Currency ID'),
            'legal_id' => Yii::t('app/book', 'Legal ID'),
            'description' => Yii::t('app/book', 'Description'),
            'dialog_id' => Yii::t('app/book', 'Dialog ID'),
            'status' => Yii::t('app/book', 'Status'),
            'created_at' => Yii::t('app/book', 'Created At'),
            'updated_at' => Yii::t('app/book', 'Updated At'),
        ];
    }


    public function beforeValidate()
    {
        if(!is_numeric($this->pay_date))
            $this->pay_date = strtotime($this->pay_date);

        return parent::beforeValidate();
    }

    public function beforeSave($insert)
    {
        if(!is_numeric($this->pay_date))
            $this->pay_date = strtotime($this->pay_date);
        return parent::beforeSave($insert);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCurrency()
    {
        return $this->hasOne(ExchangeRates::className(), ['id' => 'currency_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCuser()
    {
        return $this->hasOne(CUser::className(), ['id' => 'cntr_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOwner()
    {
        return $this->hasOne(BUser::className(), ['id' => 'owner_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getManager()
    {
        return $this->hasOne(BUser::className(), ['id' => 'manager_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLegal()
    {
        return $this->hasOne(LegalPerson::className(), ['id' => 'legal_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPayments()
    {
        return $this->hasMany(Payments::className(),['prequest_id' => 'id']);
    }

    /**
     * @return bool|string
     */
    public function getFormatedPayDate()
    {
        return date('d.m.Y H:i',$this->pay_date);
    }
}
