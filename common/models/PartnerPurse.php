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
            'withdrawal' => Yii::t('app/users', 'Withdrawal partner purse'),
            'created_at' => Yii::t('app/users', 'Created At'),
            'updated_at' => Yii::t('app/users', 'Updated At'),
            'availableAmount' => Yii::t('app/users','Amount available partner purse')
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
        return (float)$this->amount -(float)$this->withdrawal;
    }
}
