<?php

namespace common\models;

use common\components\helpers\CustomHelper;
use common\components\loggingUserBehavior\LogModelBehavior;
use devgroup\TagDependencyHelper\ActiveRecordHelper;
use DevGroup\TagDependencyHelper\NamingHelper;
use Yii;
use yii\caching\TagDependency;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

/**
 * This is the model class for table "{{%payment_condition}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $description
 * @property integer $service_id
 * @property integer $l_person_id
 * @property integer $is_resident
 * @property string $summ_from
 * @property string $summ_to
 * @property string $corr_factor
 * @property string $commission
 * @property string $sale
 * @property string $tax
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $currency_id
 * @property integer $cond_currency
 * @property integer $type
 *
 * @property Services $service
 * @property LegalPerson $lPerson
 */
class PaymentCondition extends AbstractActiveRecord
{

    public
        $is_console = false;

    CONST
        TYPE_USUAL = 5,
        TYPE_CUSTOM = 10;


    /**
     * @return array
     */
    public static function getTypeArr()
    {
        return [
            self::TYPE_USUAL => Yii::t('app/book','Type usual'),
            self::TYPE_CUSTOM => Yii::t('app/book','Type custom')
        ];
    }

    /**
     * @return string
     */
    public function getTypeStr()
    {
        $tmp = self::getTypeArr();
        return isset($tmp[$this->type]) ? $tmp[$this->type] : 'N/A';
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%payment_condition}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[
                'name',
                'description',
                'service_id',
                'l_person_id',
                'summ_from',
                'summ_to',
                'currency_id',

                'tax',
                'type',
                'cond_currency'
             ], 'required'],
            [[
                'corr_factor',
                'sale',
                'commission'
            ],'required','when' => function($model) {
                if($this->type == self::TYPE_CUSTOM) //если компания не контрагнет, то поля можно не заполнять
                    return FALSE;
                return TRUE;
            },
                'whenClient' => "function (attribute, value) {
                    var
                        type = $('#paymentcondition-type input:checked').val();

                    if(type != undefined && type == '".self::TYPE_CUSTOM."')
                    {
                        return false;
                    }
                    return true;
                }"],
            [['name'],'unique','targetClass' => self::className(),
                'message' => Yii::t('app/book','This name has already been taken.')],
            [['description'], 'string'],
            [['cond_currency','service_id', 'l_person_id', 'is_resident', 'created_at', 'updated_at','currency_id','type'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['summ_from', 'summ_to','corr_factor'],'number','min' => 0],
            [['commission', 'sale', 'tax'],'number','min' => 0],
            [['commission', 'sale', 'tax'],'number','max' => 100],
            ['is_console','integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/book', 'ID'),
            'name' => Yii::t('app/book', 'Name'),
            'description' => Yii::t('app/book', 'Description'),
            'service_id' => Yii::t('app/book', 'Service ID'),
            'l_person_id' => Yii::t('app/book', 'L Person ID'),
            'is_resident' => Yii::t('app/book', 'Is Resident'),
            'summ_from' => Yii::t('app/book', 'Summ From'),
            'summ_to' => Yii::t('app/book', 'Summ To'),
            'corr_factor' => Yii::t('app/book', 'Corr Factor'),
            'commission' => Yii::t('app/book', 'Commission'),
            'sale' => Yii::t('app/book', 'Sale'),
            'tax' => Yii::t('app/book', 'Tax'),
            'currency_id' => Yii::t('app/book', 'Currency id'),
            'created_at' => Yii::t('app/book', 'Created At'),
            'updated_at' => Yii::t('app/book', 'Updated At'),
            'cond_currency' => Yii::t('app/book', 'Condition currency'),
            'type' => Yii::t('app/book','Type')
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
    public function getLPerson()
    {
        return $this->hasOne(LegalPerson::className(), ['id' => 'l_person_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCurrency()
    {
        return $this->hasOne(ExchangeRates::className(),['id' => 'currency_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCondCurrency()
    {
        return $this->hasOne(ExchangeRates::className(),['id' => 'cond_currency']);
    }

    /**
     * @return mixed
     */
    public static function getAllCondition()
    {
        $obDep = new TagDependency([
            'tags' => [
                NamingHelper::getCommonTag(self::className()),
               // ActiveRecordHelper::getCommonTag(ExchangeRates::className())
            ]
        ]);

        return self::getDb()->cache(function($db){
           // return self::find()->with('service','currency','person')->all($db);
            return self::find()->orderBy(['service_id' => SORT_ASC,'name' => SORT_ASC])->all($db);
        },24 * 3600,$obDep);
    }

    /**
     * Получаем список условий id => название
     * @return array
     */
    public static function getConditionMap()
    {
        $arTemp = self::getAllCondition();
        return ArrayHelper::map($arTemp,'id','name');
    }

    /**
     * @return array
     */
    public static function getConditionTypeMap()
    {
        $arTemp = self::getAllCondition();
        return ArrayHelper::map($arTemp,'id','type');
    }

    /**
     * @param $date
     * @return array
     */
    public static function getConditionWithCurrency($date)
    {
        $arTmp = self::getAllCondition();
        $arCurrency = [];
        foreach($arTmp as $tmp)
            if(!in_array($tmp->cond_currency,$arCurrency))
                $arCurrency [] = $tmp->cond_currency;

        $arCurrency = array_filter($arCurrency);
        $arExch = [];
        if(!empty($arCurrency))
            foreach($arCurrency as $curr)
            {
                $arExch[$curr] = ExchangeCurrencyHistory::getCurrencyInBURForDate($date,$curr);
            }

        $arReturn = [];
        foreach($arTmp as $tmp)
        {
            $strExh = isset($arExch[$tmp->cond_currency]) ? ' <'.$arExch[$tmp->cond_currency].'>' : '';
            $arReturn[$tmp->id] = $tmp->name.$strExh;
        }

        return $arReturn;
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        $arBhvrs = parent::behaviors();
        return ArrayHelper::merge(
                $arBhvrs,
                [
                    [
                        'class' => LogModelBehavior::className(),
                        'ignored' => ['created_at','updated_at'],
                        'active' => !$this->is_console
                    ],
                ]);
    }

    /**
     * Получение id условий, которые подходят под параметры
     * @param $iServiceID
     * @param $iLegalID
     * @param $amount   -- в бел рублях
     * @param $isResident
     * @param $iPayDate  -- integer
     * @return array
     * @throws NotFoundHttpException
     */
    public static function getAppropriateConditions($iServiceID,$iLegalID,$amount,$isResident,$iPayDate)
    {
        $arResult = [];
        //получаем все условия подходящие под параметры
        $arConditions = self::find()
            ->select(['id','summ_from','summ_to','currency_id'])
            ->where([
                'service_id' => (int)$iServiceID,
                'l_person_id' => $iLegalID,
                'is_resident' => $isResident
            ])
            ->all();

        if(empty($arConditions))
            return $arResult;

        foreach($arConditions as $cond)
        {
            $curr = ExchangeCurrencyHistory::getCurrencyInBURForDate(date('Y-m-d',$iPayDate),$cond->currency_id);
            if(empty($curr))
                throw new NotFoundHttpException('Currency not found');

            $leftBorder = (float)$cond->summ_from*(float)$curr;
            $rightBorder = (float)$cond->summ_to*(float)$curr;

            if($leftBorder<=$amount && $rightBorder >= $amount)
                $arResult [] = $cond->id;
        }

        return $arResult;
    }

}
