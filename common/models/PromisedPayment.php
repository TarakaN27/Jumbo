<?php

namespace common\models;

use backend\models\BUser;
use common\components\behavior\PromisedPayment\PromisedpaymentBehavior;
use common\components\loggingUserBehavior\LogModelBehavior;
use common\components\payment\PromisedPaymentHelper;
use Yii;
use yii\caching\DbDependency;
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
 * @property integer $service_id
 * @property integer $owner
 * @property string $description
 */
class PromisedPayment extends AbstractActiveRecord
{

    CONST
        OVERDUE_DAYS = 3,
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
            ['amount','trim'],
            [['cuser_id', 'amount','service_id'], 'required'],
            [['cuser_id', 'buser_id_p', 'paid_date', 'paid', 'sort', 'created_at', 'updated_at','service_id','owner'], 'integer'],
            [['amount'], 'number', 'min' => 0],
            ['paid','default', 'value' => self::NO],
            ['description','string','max' => 255]
            //['cuser_id','customValidate','on' => [self::SCENARIO_NEW]],
            //['amount','customValAmount']
        ];
    }

    /**
     * @param $attribute
     * @param $params
     */
    /*
    public function customValAmount($attribute,$params)
    {
        $obPPHelp = new PromisedPaymentHelper($this->cuser_id,$this->service_id);
        $maxAmount = $obPPHelp->getMaxAmount();
        if($this->amount > $maxAmount)
            $this->addError($attribute,Yii::t('app/book','Amount can not be more than ').$maxAmount);
    }
*/
    /**
     * @param $attribute
     * @param $params
     */
    /*
    public function customvalidate($attribute, $params)
    {
        if(self::find()
            ->where(['cuser_id' => $this->cuser_id,'service_id' => $this->service_id])
            ->andWhere('paid != :paid or paid is NULL',[':paid' => self::YES])->exists())
            $this->addError($attribute,Yii::t('app/book','Can not add new promised payment,user has an unpaid promised payment.'));
    }
*/
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/book', 'ID'),
            'cuser_id' => Yii::t('app/book', 'Cuser ID'),
            'buser_id_p' => Yii::t('app/book', 'Buser Id P'),
            'amount' => Yii::t('app/book', 'Unit amount'),
            'paid_date' => Yii::t('app/book', 'Paid Date'),
            'paid' => Yii::t('app/book', 'Paid'),
            'created_at' => Yii::t('app/book', 'Created At'),
            'updated_at' => Yii::t('app/book', 'Updated At'),
            'service_id' => Yii::t('app/book', 'Service id'),
            'owner' => Yii::t('app/book','Added by'),
            'description' => Yii::t('app/book','Description')
        ];
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
    public function getBuser()
    {
        return $this->hasOne(BUser::className(),['id' => 'buser_id_p']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAddedBy()
    {
        return $this->hasOne(BUser::className(),['id' => 'owner']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRepay()
    {
        return $this->hasMany(PromisedPayRepay::className(),['pr_pay_id' => 'id']);
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
                PromisedpaymentBehavior::className(),   //добавление запроса на зачисление
                [
                    'class' => LogModelBehavior::className(),       //логирование платежей
                    'ignored' => ['created_at','updated_at']
                ]
            ]);
    }

    /**
     * @param $userID
     * @return mixed
     * @throws \Exception
     */
    public static function getPaymentListForUserCached($userID)
    {
        $obDep = new DbDependency([
            'sql' => 'SELECT MAX(updated_at) FROM '.self::tableName().' WHERE cuser_id = :user_id',
            'params' => [
                ':user_id' => $userID
            ]
        ]);

        return self::getDb()->cache(function($db) use ($userID){
            return self::find()
                ->where('paid != :paid OR paid is NULL',[
                    ':paid' => self::YES
                ])
                ->andWhere(['cuser_id' => $userID])->with('service')->all();
        },86400,$obDep);
    }

    /**
     * @param $userID
     * @param $iServID
     * @return mixed
     */
    public static function isPaymentExist($userID,$iServID)
    {
        return self::find()
            ->where('paid != :paid OR paid is NULL',[
                ':paid' => self::YES
            ])
            ->andWhere(['cuser_id' => $userID,'service_id' => $iServID])->exists();
    }

    /**
     * @return mixed
     */
    public static function getOverduePromisedPayment()
    {
        $time = time()-3600*24*self::OVERDUE_DAYS;

        return self::find()
           ->where('paid_date > :pay_date',[':pay_date' => $time])
            ->andWhere('paid != :paid OR paid is NULL',[
                ':paid' => self::YES
            ])
            ->with('service','cuser.requisites')
            ->all();
    }
}
