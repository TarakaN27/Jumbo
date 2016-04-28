<?php

namespace common\models;

use Yii;
use backend\models\BUser;

/**
 * This is the model class for table "{{%partner_w_bookkeeper_request}}".
 *
 * @property integer $id
 * @property integer $buser_id
 * @property integer $partner_id
 * @property integer $contractor_id
 * @property string $amount
 * @property integer $currency_id
 * @property integer $legal_id
 * @property integer $request_id
 * @property integer $created_by
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property BUser $createdBy
 * @property BUser $buser
 * @property CUser $contractor
 * @property ExchangeRates $currency
 * @property LegalPerson $legal
 * @property CUser $partner
 * @property PartnerWithdrawalRequest $request
 */
class PartnerWBookkeeperRequest extends AbstractActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%partner_w_bookkeeper_request}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['buser_id', 'partner_id', 'contractor_id', 'currency_id', 'legal_id', 'request_id', 'created_by', 'status', 'created_at', 'updated_at'], 'integer'],
            [['partner_id', 'request_id'], 'required'],
            [['amount'], 'number'],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => BUser::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['buser_id'], 'exist', 'skipOnError' => true, 'targetClass' => BUser::className(), 'targetAttribute' => ['buser_id' => 'id']],
            [['contractor_id'], 'exist', 'skipOnError' => true, 'targetClass' => CUser::className(), 'targetAttribute' => ['contractor_id' => 'id']],
            [['currency_id'], 'exist', 'skipOnError' => true, 'targetClass' => ExchangeRates::className(), 'targetAttribute' => ['currency_id' => 'id']],
            [['legal_id'], 'exist', 'skipOnError' => true, 'targetClass' => LegalPerson::className(), 'targetAttribute' => ['legal_id' => 'id']],
            [['partner_id'], 'exist', 'skipOnError' => true, 'targetClass' => CUser::className(), 'targetAttribute' => ['partner_id' => 'id']],
            [['request_id'], 'exist', 'skipOnError' => true, 'targetClass' => PartnerWithdrawalRequest::className(), 'targetAttribute' => ['request_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/users', 'ID'),
            'buser_id' => Yii::t('app/users', 'Buser ID'),
            'partner_id' => Yii::t('app/users', 'Partner ID'),
            'contractor_id' => Yii::t('app/users', 'Contractor ID'),
            'amount' => Yii::t('app/users', 'Amount'),
            'currency_id' => Yii::t('app/users', 'Currency ID'),
            'legal_id' => Yii::t('app/users', 'Legal ID'),
            'request_id' => Yii::t('app/users', 'Request ID'),
            'created_by' => Yii::t('app/users', 'Created By'),
            'status' => Yii::t('app/users', 'Status'),
            'created_at' => Yii::t('app/users', 'Created At'),
            'updated_at' => Yii::t('app/users', 'Updated At'),
        ];
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
    public function getBuser()
    {
        return $this->hasOne(BUser::className(), ['id' => 'buser_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContractor()
    {
        return $this->hasOne(CUser::className(), ['id' => 'contractor_id']);
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
    public function getLegal()
    {
        return $this->hasOne(LegalPerson::className(), ['id' => 'legal_id']);
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
    public function getRequest()
    {
        return $this->hasOne(PartnerWithdrawalRequest::className(), ['id' => 'request_id']);
    }
}
