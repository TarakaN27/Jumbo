<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%act_services}}".
 *
 * @property integer $id
 * @property integer $act_id
 * @property integer $service_id
 * @property string $amount
 * @property integer $quantity
 * @property integer $contract_date
 * @property string $contract_number
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $job_description
 *
 * @property Services $service
 * @property Acts $act
 */
class ActServices extends AbstractActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%act_services}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['act_id', 'service_id'], 'required'],
            [['act_id', 'service_id', 'quantity', 'contract_date', 'created_at', 'updated_at'], 'integer'],
            [['amount'], 'number'],
            [['contract_number'], 'string', 'max' => 255],
            [['service_id'], 'exist', 'skipOnError' => true, 'targetClass' => Services::className(), 'targetAttribute' => ['service_id' => 'id']],
            [['act_id'], 'exist', 'skipOnError' => true, 'targetClass' => Acts::className(), 'targetAttribute' => ['act_id' => 'id']],
            ['job_description','string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/book', 'ID'),
            'act_id' => Yii::t('app/book', 'Act ID'),
            'service_id' => Yii::t('app/book', 'Service ID'),
            'amount' => Yii::t('app/book', 'Amount'),
            'quantity' => Yii::t('app/book', 'Quantity'),
            'contract_date' => Yii::t('app/book', 'Contract Date'),
            'contract_number' => Yii::t('app/book', 'Contract Number'),
            'created_at' => Yii::t('app/book', 'Created At'),
            'updated_at' => Yii::t('app/book', 'Updated At'),
            'job_description' => Yii::t('app/book', 'Job description')
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getService()
    {
        return $this->hasOne(Services::className(), ['id' => 'service_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAct()
    {
        return $this->hasOne(Acts::className(), ['id' => 'act_id']);
    }
}
