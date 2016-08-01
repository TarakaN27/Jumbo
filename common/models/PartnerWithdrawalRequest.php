<?php

namespace common\models;

use common\components\customComponents\validation\ValidNumber;
use Yii;
use backend\models\BUser;
/**
 * This is the model class for table "{{%partner_withdrawal_request}}".
 *
 * @property integer $id
 * @property integer $partner_id
 * @property integer $type
 * @property string $amount
 * @property integer $currency_id
 * @property integer $manager_id
 * @property integer $created_by
 * @property pending_in_base_currency
 * @property integer $date
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $description
 *
 * @property BUser $createdBy
 * @property CUser $partner
 * @property BUser $manager
 */
class PartnerWithdrawalRequest extends AbstractActiveRecord
{
    CONST
        TYPE_MONEY = 5,                     //Type money
        TYPE_SERVICE = 10;                  //Type service

    CONST
        STATUS_NEW = 5,                     //new request
        STATUS_PROCESSING_IN_BOOKKEEPING = 10,      //when request have type SERVICE, and request goes to manager
        STATUS_DONE = 15;                   //request is done

    CONST
        SCENARIO_CREATE_REQUEST = 'create_request',
        SCENARIO_SET_MANAGER = 'set_manager';   //Scenario for set manager

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%partner_withdrawal_request}}';
    }

    /**
     * @return array
     */
    public static function getStatusMap()
    {
        return [
            self::STATUS_NEW => Yii::t('app/users', 'Status new'),
            self::STATUS_PROCESSING_IN_BOOKKEEPING => Yii::t('app/users', 'Status manager processed'),
            self::STATUS_DONE => Yii::t('app/users', 'Status done'),
        ];
    }

    /**
     * @return array
     */
    public static function getTypeMap()
    {
        return [
            self::TYPE_MONEY => Yii::t('app/users', 'Type money'),
            self::TYPE_SERVICE => Yii::t('app/users', 'Type services')
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['partner_id', 'type','amount','currency','date'], 'required'],
            ['amount',ValidNumber::className()],
            [['partner_id', 'type', 'currency_id', 'manager_id', 'created_by', 'status', 'created_at', 'updated_at'], 'integer'],
            [['amount','pending_in_base_currency'], 'number','numberPattern' => '/^\s*[-+]?[0-9\s]*[\.,\s]?[0-9]+([eE][-+]?[0-9]+)?\s*$/'],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => BUser::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['partner_id'], 'exist', 'skipOnError' => true, 'targetClass' => CUser::className(), 'targetAttribute' => ['partner_id' => 'id']],
            [['manager_id'], 'exist', 'skipOnError' => true, 'targetClass' => BUser::className(), 'targetAttribute' => ['manager_id' => 'id']],
            [['date'],'safe'],
            ['description','string','max' => 255],
            ['manager_id','required','on' => self::SCENARIO_SET_MANAGER],
            ['amount','customValidateAmount','on' => self::SCENARIO_CREATE_REQUEST]
        ];
    }

    /**
     * @param $attribute
     * @param $params
     */
    public function customValidateAmount($attribute,$params)
    {
        $obPurse = PartnerPurse::getPurse($this->partner_id);
        if(!$obPurse)
        {
            $this->addError($attribute,'Purse not found');
        }else{
            $avAmount = (float)$obPurse->getAvailableAmount();
            $curr = ExchangeCurrencyHistory::getCurrencyInBURForDate(strtotime($this->date),$this->currency_id);
            $amount = (float)$this->amount*$curr;
            if($amount > $avAmount)
            {
                $this->addError($attribute,Yii::t('app/users','Amount can not be more than available amount'));
            }
        }
    }


    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/users', 'ID'),
            'partner_id' => Yii::t('app/users', 'Partner ID'),
            'type' => Yii::t('app/users', 'Type'),
            'amount' => Yii::t('app/users', 'Amount'),
            'currency_id' => Yii::t('app/users', 'Currency ID'),
            'manager_id' => Yii::t('app/users', 'Manager ID'),
            'created_by' => Yii::t('app/users', 'Created By'),
            'date' => Yii::t('app/users', 'Partner request date'),
            'status' => Yii::t('app/users', 'Status'),
            'created_at' => Yii::t('app/users', 'Created At'),
            'updated_at' => Yii::t('app/users', 'Updated At'),
            'description' => Yii::t('app/users','Description')
        ];
    }

    /**
     * @return mixed|null
     */
    public function getStatusStr()
    {
        $tmp = self::getStatusMap();
        return isset($tmp[$this->status]) ? $tmp[$this->status] : NULL;
    }

    /**
     * @return mixed|null
     */
    public function getTypeStr()
    {
        $tmp = self::getTypeMap();
        return isset($tmp[$this->type]) ? $tmp[$this->type] : NULL;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(BUser::className(), ['id' => 'created_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPartner()
    {
        return $this->hasOne(CUser::className(), ['id' => 'partner_id']);
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
    public function getCurrency()
    {
        return $this->hasOne(ExchangeRates::className(),['id' => 'currency_id']);
    }

    /**
     * @return bool
     */
    public function beforeValidate()
    {
        if(!is_numeric($this->date))
            $this->date = strtotime($this->date);

        return parent::beforeValidate();
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if(!is_numeric($this->date))
            $this->date = strtotime($this->date);

        if($insert) {
            $this->status = self::STATUS_NEW;
            $this->created_by = Yii::$app->user->id;
            $curr = ExchangeCurrencyHistory::getCurrencyInBURForDate($this->date, $this->currency_id);
            $this->pending_in_base_currency = $this->amount * $curr;
        }
        return parent::beforeSave($insert);
    }
    public static function getAmountPendingByCuserId($id){
        $amount = static::find()->select(['pending'=>'SUM(pending_in_base_currency)'])->where(['partner_id'=>$id,'status'=>[static::STATUS_NEW, static::STATUS_PROCESSING_IN_BOOKKEEPING]])->asArray()->one();
        return $amount['pending'];
    }

    public function processBookkeeper($amount = false){
        if($amount) {
            $this->pending_in_base_currency -= $amount;
        }
        $notProcessedBookkeeperRequest = PartnerWBookkeeperRequest::find()->where(['request_id'=>$this->id, 'status'=>PartnerWBookkeeperRequest::STATUS_NEW])->exists();
        if(!$notProcessedBookkeeperRequest){
            $this->status = static::STATUS_DONE;
            $this->pending_in_base_currency = 0;
        }
        $this->save();
    }
}
