<?php

namespace common\models;

use common\components\loggingUserBehavior\LogModelBehavior;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%promised_payment}}".
 *
 * @property integer $id
 * @property integer $cuser_id
 * @property integer $buser_id_p
 * @property string $amount
 * @property integer $paid_date
 * @property integer $paid
 * @property integer $created_at
 * @property integer $updated_at
 */
class PromisedPayment extends AbstractActiveRecord
{

    CONST
        SCENARIO_NEW = 'add_new';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%promised_payment}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['cuser_id', 'amount', 'paid_date', 'paid'], 'required'],
            [['cuser_id', 'buser_id_p', 'paid_date', 'paid', 'created_at', 'updated_at'], 'integer'],
            [['amount'], 'number', 'min' => 0],

            ['cuser_id','customValidate','on' => [self::SCENARIO_NEW]]
        ];
    }

    /**
     * @param $attribute
     * @param $params
     */
    public function customvalidate($attribute, $params)
    {
        if(self::find()
            ->where(['cuser_id' => $this->cuser_id])
            ->andWhere('paid != :paid',[':paid' => self::YES])->exist())
            $this->addError($attribute,Yii::t('app/book','Can not add new promised payment,user has an unpaid promised payment.'));
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/book', 'ID'),
            'cuser_id' => Yii::t('app/book', 'Cuser ID'),
            'buser_id_p' => Yii::t('app/book', 'Buser Id P'),
            'amount' => Yii::t('app/book', 'Amount'),
            'paid_date' => Yii::t('app/book', 'Paid Date'),
            'paid' => Yii::t('app/book', 'Paid'),
            'created_at' => Yii::t('app/book', 'Created At'),
            'updated_at' => Yii::t('app/book', 'Updated At'),
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
                ]
            ]);
    }
}
