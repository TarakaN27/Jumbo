<?php

namespace common\models;

use Yii;
use backend\models\BUser;

/**
 * This is the model class for table "{{%recalculations}}".
 *
 * @property integer $id
 * @property integer $buser_id
 * @property integer $payment_id
 * @property string $begin_date
 * @property integer $type
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property Payments $payment
 * @property BUser $buser
 */
class Recalculations extends AbstractActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%recalculations}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['buser_id', 'payment_id', 'type', 'created_at', 'updated_at'], 'integer'],
            [['begin_date'], 'safe'],
            [['type'], 'required'],
            [['payment_id'], 'exist', 'skipOnError' => true, 'targetClass' => Payments::className(), 'targetAttribute' => ['payment_id' => 'id']],
            [['buser_id'], 'exist', 'skipOnError' => true, 'targetClass' => BUser::className(), 'targetAttribute' => ['buser_id' => 'id']],
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
            'payment_id' => Yii::t('app/users', 'Payment ID'),
            'begin_date' => Yii::t('app/users', 'Begin Date'),
            'type' => Yii::t('app/users', 'Type'),
            'created_at' => Yii::t('app/users', 'Created At'),
            'updated_at' => Yii::t('app/users', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPayment()
    {
        return $this->hasOne(Payments::className(), ['id' => 'payment_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBuser()
    {
        return $this->hasOne(BUser::className(), ['id' => 'buser_id']);
    }
}
