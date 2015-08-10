<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%exchange_currency_history}}".
 *
 * @property integer $id
 * @property integer $currency_id
 * @property string $rate_nbrb
 * @property string $old_rate_nbrb
 * @property string $rate_cbr
 * @property string $old_rate_cbr
 * @property integer $user_id
 * @property string $date
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property ExchangeRates $currency
 */
class ExchangeCurrencyHistory extends AbstractActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%exchange_currency_history}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['currency_id', 'user_id', 'created_at', 'updated_at'], 'integer'],
            [['rate_nbrb', 'old_rate_nbrb', 'rate_cbr', 'old_rate_cbr'], 'required'],
            [['rate_nbrb', 'old_rate_nbrb', 'rate_cbr', 'old_rate_cbr'], 'number'],
            [['date'], 'safe'],

        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/services', 'ID'),
            'currency_id' => Yii::t('app/services', 'Currency ID'),
            'rate_nbrb' => Yii::t('app/services', 'Rate Nbrb'),
            'old_rate_nbrb' => Yii::t('app/services', 'Old Rate Nbrb'),
            'rate_cbr' => Yii::t('app/services', 'Rate Cbr'),
            'old_rate_cbr' => Yii::t('app/services', 'Old Rate Cbr'),
            'user_id' => Yii::t('app/services', 'User ID'),
            'date' => Yii::t('app/services', 'Date'),
            'created_at' => Yii::t('app/services', 'Created At'),
            'updated_at' => Yii::t('app/services', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCurrency()
    {
        return $this->hasOne(ExchangeRates::className(), ['id' => 'currency_id']);
    }
}
