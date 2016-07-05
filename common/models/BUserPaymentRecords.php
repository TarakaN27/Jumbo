<?php

namespace common\models;

use Yii;
use backend\models\BUser;

/**
 * This is the model class for table "{{%b_user_payment_records}}".
 *
 * @property integer $id
 * @property integer $buser_id
 * @property string $amount
 * @property string $record_date
 * @property integer $is_record
 * @property integer $record_num
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $percents
 *
 * @property BUser $buser
 */
class BUserPaymentRecords extends AbstractActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%b_user_payment_records}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['buser_id'], 'required'],
            [['buser_id', 'is_record', 'record_num', 'created_at', 'updated_at'], 'integer'],
            [['amount','percents'], 'number'],
            [['record_date'], 'safe'],
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
            'amount' => Yii::t('app/users', 'Turnover amount'),
            'record_date' => Yii::t('app/users', 'Record Date'),
            'is_record' => Yii::t('app/users', 'Is Record'),
            'record_num' => Yii::t('app/users', 'Record Num'),
            'created_at' => Yii::t('app/users', 'Created At'),
            'updated_at' => Yii::t('app/users', 'Updated At'),
            'percents' => Yii::t('app/users','Increment rate percents')
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBuser()
    {
        return $this->hasOne(BUser::className(), ['id' => 'buser_id']);
    }
}
