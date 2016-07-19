<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%partner_purse}}".
 *
 * @property integer $id
 * @property integer $cuser_id
 * @property string $amount
 * @property string $withdrawal
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property CUser $cuser
 */
class PartnerPurse extends AbstractActiveRecord
{
    public $pendingAmount = false;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%partner_purse}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['cuser_id'], 'required'],
            [['cuser_id', 'created_at', 'updated_at'], 'integer'],
            [['amount', 'withdrawal'], 'number'],
            [['cuser_id'], 'unique'],
            [['cuser_id'], 'exist', 'skipOnError' => true, 'targetClass' => CUser::className(), 'targetAttribute' => ['cuser_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/users', 'ID'),
            'cuser_id' => Yii::t('app/users', 'Cuser ID'),
            'amount' => Yii::t('app/users', 'Amount partner purse'),
            'totalExpenseAmount' => Yii::t('app/users', 'Withdrawal partner purse'),
            'created_at' => Yii::t('app/users', 'Created At'),
            'updated_at' => Yii::t('app/users', 'Updated At'),
            'availableAmount' => Yii::t('app/users','Amount available partner purse'),
            'totalIncomingAmount'=> Yii::t('app/users','Amount partner purse'),
            'pendingAmount' => Yii::t('app/users','Pending Amount'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCuser()
    {
        return $this->hasOne(CUser::className(), ['id' => 'cuser_id']);
    }

    /**
     * @return float
     */
    public function getAvailableAmount()
    {
        $pendingAmount = $this->getPendingAmount();
        return (float)$this->amount - $pendingAmount;
    }

    public function getPendingAmount()
    {
        if($this->pendingAmount === false) {
            $pendingAmount = PartnerWithdrawalRequest::getAmountPendingByCuserId($this->cuser_id);
            $this->pendingAmount = $pendingAmount;
        }
        return $this->pendingAmount;
    }

    /**
     * @return float
     */
    public function getTotalIncomingAmount(){
        return PartnerPurseHistory::getTotalIncomingByCUserId($this->cuser_id);
    }

    /**
     * @return float
     */
    public function getTotalExpenseAmount(){
        return PartnerPurseHistory::getTotalExpenseByCUserId($this->cuser_id);
    }

    /**
     * @param $iPartnerId
     * @return PartnerPurse
     */
    public static function getPurse($iPartnerId)
    {
        $obPurse = self::find()->where(['cuser_id' => $iPartnerId])->one();
        if(!$obPurse)
        {
            $obPurse = new PartnerPurse();
            $obPurse->amount = 0;
            $obPurse->withdrawal = 0;
            $obPurse->cuser_id = $iPartnerId;
        }
        return $obPurse;
    }

}
