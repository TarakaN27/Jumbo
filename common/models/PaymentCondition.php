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
use common\components\customComponents\validation\ValidNumber;
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
 * @property integer $not_use_sale
 * @property integer $not_use_corr_factor
 * @property integer enroll_unit_id
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
                'cond_currency',
                'status'
             ], 'required'],
            [['commission', 'sale', 'tax','summ_from', 'summ_to','corr_factor'],ValidNumber::className()],
            [[
                'corr_factor',
                'sale',
                'commission'
            ],'required','when' => function($model) {
                if($model->type == self::TYPE_CUSTOM) //если компания не контрагнет, то поля можно не заполнять
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
            [[
                'dub_enroll_unit_id',
                'dub_cond_currency',
            ],'required','when' => function($model) {
                if($model->is_dub_currency == 1)
                    return true;
                return false;
            },
                'whenClient' => "function (attribute, value) {
                    var type = $('#paymentcondition-is_dub_currency:checked').val();                  
                    if(type == 1)
                    {
                        return true;
                    }
                    return false;
                }"],
            [['name'],'unique','targetClass' => self::className(),
                'message' => Yii::t('app/book','This name has already been taken.')],
            [['description'], 'string'],
            [['cond_currency','is_dub_currency','service_id', 'l_person_id', 'is_resident', 'created_at', 'updated_at','currency_id','type', 'enroll_unit_id'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['summ_from', 'summ_to',],'number','min' => 0,'numberPattern' => '/^\s*[-+]?[0-9\s]*[\.,\s]?[0-9]+([eE][-+]?[0-9]+)?\s*$/'],
            [['commission', 'tax','corr_factor'],'number', 'numberPattern' => '/^\s*[-+]?[0-9\s]*[\.,\s]?[0-9]+([eE][-+]?[0-9]+)?\s*$/'],
            [['commission', 'sale', 'tax'],'number','max' => 100,'numberPattern' => '/^\s*[-+]?[0-9\s]*[\.,\s]?[0-9]+([eE][-+]?[0-9]+)?\s*$/'],
            ['is_console','safe'],
            [['not_use_sale', 'not_use_corr_factor','status'],'integer']
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
            'type' => Yii::t('app/book','Type'),
            'not_use_sale' => Yii::t('app/services','Not use sale with counting unit enrollment'),
            'not_use_corr_factor' => Yii::t('app/services','Not user correcting factor with counting unit enrollment'),
            'enroll_unit_id' => Yii::t('app/services','Unit enrollment'),
            'status' => Yii::t('app/book', 'Status'),
            'is_dub_currency' => Yii::t('app/book', 'Is dub currency'),
            'dub_enroll_unit_id' => Yii::t('app/book', 'Dub enroll unit id'),
            'dub_cond_currency' => Yii::t('app/book', 'Dub cond currency id'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getService()
    {
        return $this->hasOne(Services::className(), ['id' => 'service_id']);
    }

    public function getUnitEnroll()
    {
        return $this->hasOne(UnitsEnroll::className(), ['id' => 'enroll_unit_id']);
    }

    public function getDubUnitEnroll()
    {
        return $this->hasOne(UnitsEnroll::className(), ['id' => 'dub_enroll_unit_id']);
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

    public function getDubCondCurrency()
    {
        return $this->hasOne(ExchangeRates::className(),['id' => 'dub_cond_currency']);
    }

    /**
     * @return mixed
     */
    public static function getAllCondition()
    {
        return self::find()->where(['status'=>static::YES])->orderBy(['service_id' => SORT_ASC,'name' => SORT_ASC])->all();
    }

    /**
     * @return mixed
     */
    public static function getAllConditionArray()
    {
        return ArrayHelper::map(static::find()->all(), 'id','name');
    }

    /**
     * Получаем список условий id => название
     * @return array
     */
    public static function getConditionMap($current=false)
    {
        $arTemp = self::getAllCondition();
        $arMap = ArrayHelper::map($arTemp,'id','name');
        if($current && !isset($arMap[$current])){
            $arMap[$current] = "";
        }
        return $arMap;
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
    public static function getConditionWithCurrency($date, $selectedCondition = false)
    {
        $arTmp = self::getAllCondition();
        $arCurrency = [];
        if($selectedCondition){
            $ids = ArrayHelper::getColumn($arTmp,'id');
            if(!in_array($selectedCondition,$ids)){
                $notActiveCondition = self::findOne($selectedCondition);
                $notActiveCondition->name = Yii::t('app/book','Not active').' - '.$notActiveCondition->name;
                $arTmp[]= $notActiveCondition;
            }
        }
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
            $strExh = isset($arExch[$tmp->cond_currency]) ? ' <'.Yii::$app->formatter->asDecimal($arExch[$tmp->cond_currency],4).'>' : '';
            $arReturn[$tmp->id] = $tmp->name.$strExh;
        }

        if($selectedCondition && !array_key_exists($selectedCondition, $arReturn)){
            $arReturn[$selectedCondition] = "Не активно";
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
                'is_resident' => $isResident,
                'status' => static::YES,
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
