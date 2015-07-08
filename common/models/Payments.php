<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%payments}}".
 *
 * @property integer $id
 * @property integer $cuser_id
 * @property integer $pay_date
 * @property string $pay_summ
 * @property integer $currency_id
 * @property integer $service_id
 * @property integer $legal_id
 * @property string $description
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property ExchangeRates $currency
 * @property CUser $cuser
 * @property LegalPerson $legal
 * @property Services $service
 */
class Payments extends AbstractActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%payments}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['cuser_id', 'pay_date', 'pay_summ', 'currency_id', 'service_id', 'legal_id'], 'required'],
            [['cuser_id', 'currency_id', 'service_id', 'legal_id', 'created_at', 'updated_at'], 'integer'],
            [['pay_summ'], 'number'],
            [['description'], 'string']
        ];
    }

    /**
     * @return bool
     */
    public function beforeValidate()
    {
        if(!is_numeric($this->pay_date))
            $this->pay_date = strtotime($this->pay_date);

        return parent::beforeValidate();
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if(!is_numeric($this->pay_date))
            $this->pay_date = strtotime($this->pay_date);
        return parent::beforeSave($insert);
    }


    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/book', 'ID'),
            'cuser_id' => Yii::t('app/book', 'Cuser ID'),
            'pay_date' => Yii::t('app/book', 'Pay Date'),
            'pay_summ' => Yii::t('app/book', 'Pay Summ'),
            'currency_id' => Yii::t('app/book', 'Currency ID'),
            'service_id' => Yii::t('app/book', 'Service ID'),
            'legal_id' => Yii::t('app/book', 'Legal ID'),
            'description' => Yii::t('app/book', 'Description'),
            'created_at' => Yii::t('app/book', 'Created At'),
            'updated_at' => Yii::t('app/book', 'Updated At'),
        ];
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
    public function getCuser()
    {
        return $this->hasOne(CUser::className(), ['id' => 'cuser_id']);
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
    public function getService()
    {
        return $this->hasOne(Services::className(), ['id' => 'service_id']);
    }

    /**
     * @return bool|string
     */
    public function getFormatedPayDate()
    {
        return date('d.m.Y H:i',$this->pay_date);
    }
}
