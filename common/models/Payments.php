<?php

namespace common\models;

use common\components\customComponents\validation\ValidNumber;
use common\components\loggingUserBehavior\LogModelBehavior;
use common\components\payment\PaymentBonusBehavior;
use common\components\payment\PaymentEnrollmentBehavior;
use common\components\payment\PaymentQuantityHoursBehavior;
use common\components\payment\PaymentRecalculatePartnerPercentBehavior;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%payments}}".
 *
 * @property integer $id
 * @property integer $cuser_id
 * @property integer $pay_date
 * @property string $pay_summ
 * @property integer $currency_id
 * @property integer $service_id
 * @property integer $legal_id
 * @property string $description
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $prequest_id
 * @property string $payment_order
 * @property integer $act_close
 * @property integer $hide_act_payment
 *
 * @property ExchangeRates $currency
 * @property CUser $cuser
 * @property LegalPerson $legal
 * @property Services $service
 */
class Payments extends AbstractActiveRecord
{
    CONST
        SCENARIO_ACT_CLOSE = 'act_closes';

    CONST
        WITHOUT_ENROLL = 0,
        ENROLL_NO = 1,
        ENROLL_YES = 2,
        ENROLL_PART = 3;

    public
        $enrollStatus = 0,
        $actAmount = 0,
        $isSale = false,
        $saleUser,
        $customProd,
        $showAll,
        $updateWithNewCondition,
        $condition_id;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%payments}}';
    }

    /**
     * @return array
     */
    public static function getEnrollStatusMap()
    {
        return [
            self::WITHOUT_ENROLL => '-',
            self::ENROLL_YES => Yii::t('app/book','Enroll yes'),
            self::ENROLL_NO => Yii::t('app/book','Enroll no'),
            self::ENROLL_PART => Yii::t('app/book','Enroll part')
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[
                'payment_order','cuser_id',
                'pay_date', 'pay_summ',
                'currency_id', 'service_id',
                'legal_id','condition_id'
            ], 'required', 'except' => [self::SCENARIO_ACT_CLOSE]],
            [['pay_summ','customProd'],ValidNumber::className()],
            [['pay_summ','customProd'], 'number','numberPattern' => '/^\s*[-+]?[0-9\s]*[\.,\s]?[0-9]+([eE][-+]?[0-9]+)?\s*$/'],
            [[
                'cuser_id', 'currency_id',
                'service_id', 'legal_id',
                'created_at', 'updated_at',
                'prequest_id','condition_id',
                'updateWithNewCondition',
                'act_close','hide_act_payment'
            ], 'integer'],
            [['actAmount'], 'number'],
            [['description'], 'string'],
            ['showAll','safe']
        ];
    }

    /**
     * @return bool
     */
    public function beforeValidate()
    {
        if(!is_numeric($this->pay_date))
            $this->pay_date = strtotime($this->pay_date);

        return parent::beforeValidate();
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if(!is_numeric($this->pay_date))
            $this->pay_date = strtotime($this->pay_date);
        return parent::beforeSave($insert);
    }
    
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/book', 'ID'),
            'cuser_id' => Yii::t('app/book', 'Cuser ID'),
            'pay_date' => Yii::t('app/book', 'Pay Date'),
            'pay_summ' => Yii::t('app/book', 'Pay Summ'),
            'currency_id' => Yii::t('app/book', 'Currency ID'),
            'service_id' => Yii::t('app/book', 'Service ID'),
            'legal_id' => Yii::t('app/book', 'Legal ID'),
            'description' => Yii::t('app/book', 'Description'),
            'created_at' => Yii::t('app/book', 'Created At'),
            'updated_at' => Yii::t('app/book', 'Updated At'),
            'prequest_id' => Yii::t('app/book', 'Payment request'),
            'condition_id' => Yii::t('app/book', 'Condition'),
            'updateWithNewCondition' => Yii::t('app/book', 'Update with new condition'),
            'payment_order' => Yii::t('app/book','Payment order'),
            'showAll' => Yii::t('app/book','Show all conditions'),
            'customProd' => Yii::t('app/book','Custom amount production'),
            'act_close' => Yii::t('app/book','Act close'),
            'actAmount' => Yii::t('app/book','Act amount'),
            'hide_act_payment' => Yii::t('app/book','Hide payment at act'),
            'enrollStatus' => Yii::t('app/book','Enrollment status')
        ];
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
                    'class' => LogModelBehavior::className(),       //логирование платежей
                    'ignored' => ['created_at','updated_at']
                ],
                PaymentBonusBehavior::className(),  //бонусы пользователям
                /* отключили 11/02/2016
                [
                    'class' => PaymentPredefinedConditionBehavior::className()  //устанавливаем предопределныеусловия для CUSER
                ],
                */
                [
                    'class' => PaymentQuantityHoursBehavior::className()            //начисление норма часов
                ],
                [
                    'class' => PaymentEnrollmentBehavior::className()               //запрос на зачисление
                ],
                [
                    'class' => PaymentRecalculatePartnerPercentBehavior::className()    //перерасчет партнерских бонусов(добавление флагов в таблицу для пересчета)
                ]
            ]);
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
        return $this->hasOne(CUser::className(), ['id' => 'cuser_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLegal()
    {
        return $this->hasOne(LegalPerson::className(), ['id' => 'legal_id']);
    }

    public function getCondition()
    {
        return $this->hasOne(PaymentCondition::className(),['id' => 'condition_id']);
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
    public function getCalculate()
    {
        return $this->hasOne(PaymentsCalculations::className(),['payment_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPayRequest()
    {
        return $this->hasOne(PaymentRequest::className(),['id' => 'prequest_id']);
    }


    /**
     * @return bool|string
     */
    public function getFormatedPayDate()
    {
        return date('d.m.Y H:i',$this->pay_date);
    }

    /**
     * @param $userID
     * @param $period
     * @return mixed
     */
    public static function getPaymentByLastPeriod($userID,$period)
    {
        return self::find()   //получаем платежи пользователя за последние 3 месяца.
            ->where('pay_date > :pay_date',[':pay_date' => $period])
            ->andWhere(['cuser_id' => $userID])
            ->all();
    }

    /**
     * @return mixed|null
     */
    public function getEnrollStatusStr()
    {
        $arTmp = self::getEnrollStatusMap();
        return isset($arTmp[$this->enrollStatus]) ? $arTmp[$this->enrollStatus] : NULL;
    }
    
}
