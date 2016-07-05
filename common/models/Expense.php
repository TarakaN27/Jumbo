<?php

namespace common\models;

use Yii;
use common\components\helpers\CustomHelperMoney;
/**
 * This is the model class for table "{{%expense}}".
 *
 * @property integer $id
 * @property integer $pay_date
 * @property string $pay_summ
 * @property integer $currency_id
 * @property integer $legal_id
 * @property integer $cuser_id
 * @property integer $cat_id
 * @property string $description
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $pw_request_id
 *
 * @property ExchangeRates $currency
 * @property ExpenseCategories $cat
 * @property CUser $cuser
 * @property LegalPerson $legal
 */
class Expense extends AbstractActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%expense}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[
                'pay_date', 'pay_summ', 'currency_id',
                'legal_id',  'cat_id'
            ], 'required'],
            [[
                'pay_date', 'currency_id',
                'legal_id', 'cuser_id',
                'cat_id', 'created_at',
                'updated_at','pw_request_id'
            ], 'integer'],
            [['pay_summ'], 'number','numberPattern' => '/^\s*[-+]?[0-9\s]*[\.,\s]?[0-9]+([eE][-+]?[0-9]+)?\s*$/'],
            [['description'], 'string'],
            [['cuser_id'],'required','when' => function(){
                $obCat = $this->cat;
                if(is_object($obCat))
                {
                    if($obCat->without_cuser)
                        return FALSE;
                }
                return TRUE;
            }]

        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/book', 'ID'),
            'pay_date' => Yii::t('app/book', 'Pay Date'),
            'pay_summ' => Yii::t('app/book', 'Pay Summ'),
            'currency_id' => Yii::t('app/book', 'Currency ID'),
            'legal_id' => Yii::t('app/book', 'Legal ID'),
            'cuser_id' => Yii::t('app/book', 'Cuser ID'),
            'cat_id' => Yii::t('app/book', 'Cat ID'),
            'description' => Yii::t('app/book', 'Description'),
            'created_at' => Yii::t('app/book', 'Created At'),
            'updated_at' => Yii::t('app/book', 'Updated At'),
            'pw_request_id' => Yii::t('app/book','Partner withdrawal request id')
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
     * @return \yii\db\ActiveQuery
     */
    public function getCurrency()
    {
        return $this->hasOne(ExchangeRates::className(), ['id' => 'currency_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCat()
    {
        return $this->hasOne(ExpenseCategories::className(), ['id' => 'cat_id']);
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
     * @return bool|string
     */
    public function getFormatedPayDate()
    {
        return date('d.m.Y',$this->pay_date);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPartnerWithdrawalRequest()
    {
        return $this->hasOne(PartnerWithdrawalRequest::className(),['id' => 'pw_request_id']);
    }

    /**
     *
     */
    public function convertToValidAmount()
    {
        $this->pay_summ = CustomHelperMoney::convertFromBynToBur($this->pay_summ,$this->currency_id);
    }

    /**
     *
     */
    public function convertToInavlidAmount()
    {
        $this->pay_summ = CustomHelperMoney::convertFromBurToByn($this->pay_summ,$this->currency_id);
    }
}
