<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%partner_purse_history}}".
 *
 * @property integer $id
 * @property integer $cuser_id
 * @property string $amount
 * @property integer $type
 * @property integer $payment_id
 * @property integer $expense_id
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $percent
 *
 * @property Expense $expense
 * @property CUser $cuser
 * @property Payments $payment
 */
class PartnerPurseHistory extends AbstractActiveRecord
{
    CONST
        TYPE_INCOMING = 5,
        TYPE_EXPENSE =10;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%partner_purse_history}}';
    }

    /**
     * @return array
     */
    public static function getTypeMap()
    {
        return [
            self::TYPE_INCOMING => Yii::t('app/users','Partner type incoming'),
            self::TYPE_EXPENSE => Yii::t('app/users','Partner type expense')
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['cuser_id', 'amount'], 'required'],
            [['cuser_id', 'type', 'payment_id', 'expense_id', 'created_at', 'updated_at'], 'integer'],
            [['amount','percent'], 'number'],
            [['expense_id'], 'exist', 'skipOnError' => true, 'targetClass' => Expense::className(), 'targetAttribute' => ['expense_id' => 'id']],
            [['cuser_id'], 'exist', 'skipOnError' => true, 'targetClass' => CUser::className(), 'targetAttribute' => ['cuser_id' => 'id']],
            [['payment_id'], 'exist', 'skipOnError' => true, 'targetClass' => Payments::className(), 'targetAttribute' => ['payment_id' => 'id']],
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
            'amount' => Yii::t('app/users', 'Amount'),
            'type' => Yii::t('app/users', 'Type'),
            'payment_id' => Yii::t('app/users', 'Payment ID'),
            'expense_id' => Yii::t('app/users', 'Expense ID'),
            'created_at' => Yii::t('app/users', 'Created At'),
            'updated_at' => Yii::t('app/users', 'Updated At'),
            'percent' => Yii::t('app/users','Percent')
        ];
    }

    public function getTypeStr()
    {
        $tmp = self::getTypeMap();
        return $tmp[$this->type] ? $tmp[$this->type] : NULL;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getExpense()
    {
        return $this->hasOne(Expense::className(), ['id' => 'expense_id']);
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
    public function getPayment()
    {
        return $this->hasOne(Payments::className(), ['id' => 'payment_id']);
    }
}
