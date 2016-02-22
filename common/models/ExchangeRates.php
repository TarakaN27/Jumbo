<?php

namespace common\models;

use devgroup\TagDependencyHelper\ActiveRecordHelper;
use DevGroup\TagDependencyHelper\NamingHelper;
use Yii;
use yii\caching\DbDependency;
use yii\caching\TagDependency;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%exchange_rates}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $code
 * @property integer $nbrb
 * @property integer $cbr
 * @property string $nbrb_rate
 * @property string $cbr_rate
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $need_upd
 * @property integer $is_default
 * @property integer $base_id
 * @property integer $use_base
 * @property integer $use_exchanger
 * @property integer $bank_id
 * @property string $factor
 * @property integer $use_rur_for_byr
 */
class ExchangeRates extends AbstractActiveRecord
{

    protected
        $_oldModelAttribute;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%exchange_rates}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'code', 'nbrb', 'cbr', 'nbrb_rate', 'cbr_rate'], 'required'],

            ['name', 'unique', 'targetClass' => self::className(),
             'message' => Yii::t('app/services','This name has already been taken.')],

            ['code', 'unique', 'targetClass' => self::className(),
             'message' => Yii::t('app/services','This code has already been taken.')],

            [[
                 'nbrb', 'cbr', 'created_at', 'updated_at',
                 'need_upd','is_default','use_base','base_id',
                 'use_exchanger','bank_id','use_rur_for_byr'
            ],'integer'],
            [['nbrb_rate', 'cbr_rate','factor'], 'number'],
            [['name', 'code'], 'string', 'max' => 255],
            ['factor','number','min' => 0],
            [['factor'],'default','value' => 1],
            [['base_id', 'factor'],
             'required',
             'when' => function($model) {
                     return $model->use_base ? TRUE : FALSE;
                 },
             'whenClient' => "function (attribute, value) {
                    return $('#exchangerates-use_base').is(':checked');
                }"
            ],
            [['bank_id','factor'],'required',
             'when' => function($model) {
                     return $model->use_exchanger ? TRUE : FALSE;
                 },
             'whenClient' => "function (attribute, value) {
                    return $('#exchangerates-use_exchanger').is(':checked');
                }"
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/services', 'ID'),
            'name' => Yii::t('app/services', 'Name'),
            'code' => Yii::t('app/services', 'Code'),
            'nbrb' => Yii::t('app/services', 'Nbrb'),
            'cbr' => Yii::t('app/services', 'Cbr'),
            'nbrb_rate' => Yii::t('app/services', 'Nbrb Rate'),
            'cbr_rate' => Yii::t('app/services', 'Cbr Rate'),
            'created_at' => Yii::t('app/services', 'Created At'),
            'updated_at' => Yii::t('app/services', 'Updated At'),
            'need_upd' => Yii::t('app/services', 'Auto update'),
            'is_default' => Yii::t('app/services', 'Is default'),
            'use_base' => Yii::t('app/services', 'Use base'),
            'base_id' => Yii::t('app/services', 'Base ID'),
            'factor' => Yii::t('app/services', 'Factor'),
            'bank_id' => Yii::t('app/services', 'Bank ID'),
            'use_exchanger' => Yii::t('app/services', 'Use exchanger'),
            'use_rur_for_byr' => Yii::t('app/services', 'Use currency rur for count byr')
        ];
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

            ]);
    }

    /**
     * Получаем все курсы валют, закешированы
     * @return mixed
     */
    public static function getExchangeRates()
    {
        $dep =  new TagDependency(['tags' => NamingHelper::getCommonTag(self::className()),]);
        $models = self::getDb()->cache(function ($db) {
            return ExchangeRates::find()->orderBy(['is_default' => SORT_DESC])->all($db);
        },86400,$dep);

        return $models;
    }

    /**
     * @param null $exept
     * @return array
     */
    public static function getRatesCodes($exept = NULL)
    {
        $arTmp = self::getExchangeRates();

        if(!is_null($exept))
            foreach($arTmp as $key => $tmp)
                if($tmp->id == $exept)
                    unset($arTmp[$key]);

        return ArrayHelper::map($arTmp,'id','code');
    }

    /**
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        if($this->is_default == self::YES)
        {
            self::updateAll(['is_default' => self::NO],'id != :id',[':id'=>$this->id]);
        }
        $this->saveChangedValue();
        return parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @return string
     */
    public function getNeedUpdateStr()
    {
        return $this->getYesNoStr($this->need_upd);
    }

    /**
     * @return string
     */
    public function getIsDefaultStr()
    {
        return $this->getYesNoStr($this->is_default);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBase()
    {
        return $this->hasOne(self::className(),['id'=>'base_id']);
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if(parent::beforeSave($insert))
        {
            $this->_oldModelAttribute = $this->oldAttributes;
            return TRUE;
        }
        return FALSE;
    }

    /**
     * @return bool
     */
    protected function saveChangedValue()
    {
        //получим старые значения аттрибутов
        $old_rate_nbrb = isset($this->_oldModelAttribute['nbrb_rate']) ? $this->_oldModelAttribute['nbrb_rate'] : 0;
        $old_rate_cbr = isset($this->_oldModelAttribute['cbr_rate']) ? $this->_oldModelAttribute['cbr_rate'] : 0;
        if($old_rate_nbrb == $this->nbrb_rate && $old_rate_cbr == $this->cbr_rate)
            return true;
        //получим пользователя, который меняет. Если меняется из консоли, то userID = NULL
        $userID = NULL;
        $app = Yii::$app;
        if(property_exists($app,'user') && !Yii::$app->user->isGuest)
            $userID = Yii::$app->user->id;
        //дата изменения
        $date = date('Y-m-d',time());

        //в один день может быть только один курс!
        /** @var ExchangeCurrencyHistory $obH */
        $obH = ExchangeCurrencyHistory::findOne(['currency_id' => $this->id,'date' => $date]);
        if(empty($obH))
            $obH = new ExchangeCurrencyHistory();

        $obH->currency_id = $this->id;
        $obH->date = $date;
        $obH->user_id = $userID;
        $obH->old_rate_nbrb = $old_rate_nbrb;
        $obH->old_rate_cbr = $old_rate_cbr;
        $obH->rate_nbrb = $this->nbrb_rate;
        $obH->rate_cbr = $this->cbr_rate;
        //сохраняем историю
        return $obH->save();
    }


}
