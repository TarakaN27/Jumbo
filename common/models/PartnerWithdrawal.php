<?php

namespace common\models;

use Yii;
use yii\caching\TagDependency;
/**
 * This is the model class for table "{{%partner_withdrawal}}".
 *
 * @property integer $id
 * @property integer $partner_id
 * @property string $amount
 * @property integer $type
 * @property string $description
 * @property integer $created_at
 * @property integer $updated_at
 */
class PartnerWithdrawal extends AbstractActiveRecord
{

    CONST
        TYPE_SERVICE = 1,
        TYPE_MONEY = 2;

    protected
        $diffAmount = 0;


    /**
     * @return array
     */
    public static function getTypeArr()
    {
        return [
            self::TYPE_SERVICE => Yii::t('app/book', 'Type service'),
            self::TYPE_MONEY => Yii::t('app/book', 'Type money')
        ];
    }

    /**
     * @return string
     */
    public function getTypeStr()
    {
        $arTmp = self::getTypeArr();

        return isset($arTmp[$this->type]) ? $arTmp[$this->type] : 'N/A';
    }


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%partner_withdrawal}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['partner_id', 'amount'], 'required'],
            [['partner_id', 'type', 'created_at', 'updated_at'], 'integer'],
            ['type', 'in', 'range' => array_keys(self::getTypeArr())],
            [['amount'], 'number', 'min' => 0],
            [['description'], 'string'],
            ['amount', 'validateAvailableSum'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/book', 'ID'),
            'partner_id' => Yii::t('app/book', 'Partner ID'),
            'amount' => Yii::t('app/book', 'Amount withdrawal'),
            'type' => Yii::t('app/book', 'Type'),
            'description' => Yii::t('app/book', 'Description'),
            'created_at' => Yii::t('app/book', 'Created At'),
            'updated_at' => Yii::t('app/book', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPartner()
    {
        return $this->hasOne(Partner::className(),['id' => 'partner_id']);
    }

    /**
     * @param $attribute
     * @param $params
     */
    public function validateAvailableSum($attribute, $params)
    {
        if($this->isNewRecord || $this->isAttributeChanged('amount'))
        {
            /** @var $obpPPurse PartnerPurse   */
            //$obp = PartnerManager::findOne($this->user_id);
            $obpPPurse = PartnerPurse::findOne(['partner_id' => $this->partner_id]);
            if(empty($obpPPurse))
                $this->addError($attribute,Yii::t('app/book','Partner purse not found. Can not count available sum.'));

            $iASum = $obpPPurse->amount;

            if($this->isNewRecord) //если новая запись
            {
                if ($this->$attribute > $iASum) {
                    $this->addError($attribute,Yii::t('app/book', 'Not enough available sum. Available: ').' '.$iASum);
                }
            }

            if($this->isAttributeChanged('amount')) //если изменили сумму
            {
                $oldAmount = $this->getOldAttribute('amount'); //получаем старое значение
                if($oldAmount < $this->amount)
                {
                    $diff = $this->amount - $oldAmount;
                    if($diff > $iASum)
                        $this->addError($attribute,Yii::t('app/book', 'Not enough available sum. Available: ').' '.($iASum+$oldAmount));
                }
            }
        }
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if(!$insert) //если редактируется модель, нужно запомнить разницу суммы
            $this->diffAmount = $this->amount-$this->getOldAttribute('amount');
        return parent::beforeSave($insert);
    }

    /**
     * @return int
     */
    public function getDiffAmount()
    {
        return $this->diffAmount;
    }

    /**
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        //инвалидируем кеш для определенного партнера
        if(!$insert)
            TagDependency::invalidate(Yii::$app->cache,[self::getTagName('partner_id',$this->partner_id)]);
        return parent::afterSave($insert, $changedAttributes);
    }


}
