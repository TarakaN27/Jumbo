<?php

namespace common\models;

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
 * @property integer $date
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property BUser $createdBy
 * @property CUser $partner
 * @property BUser $manager
 */
class PartnerWithdrawalRequest extends AbstractActiveRecord
{
    CONST
        TYPE_MONEY = 5,                 //Type money
        TYPE_SERVICE = 10;              //Type service

    CONST
        STATUS_NEW = 5,                 //new request
        STATUS_MANAGER_PROCESSED = 10,  //when request have type SERVICE, and request goes to manager
        STATUS_DONE = 15;               //request is done

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
            self::STATUS_MANAGER_PROCESSED => Yii::t('app/users', 'Status manager processed'),
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
            [['partner_id', 'type'], 'required'],
            [['partner_id', 'type', 'currency_id', 'manager_id', 'created_by', 'status', 'created_at', 'updated_at'], 'integer'],
            [['amount'], 'number'],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => BUser::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['partner_id'], 'exist', 'skipOnError' => true, 'targetClass' => CUser::className(), 'targetAttribute' => ['partner_id' => 'id']],
            [['manager_id'], 'exist', 'skipOnError' => true, 'targetClass' => BUser::className(), 'targetAttribute' => ['manager_id' => 'id']],
            [['date'],'safe']
        ];
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

        if($insert)
            $this->status = self::STATUS_NEW;

        if($insert)
            $this->created_by = Yii::$app->user->id;

        return parent::beforeSave($insert);
    }
}
