<?php

namespace common\models;

use devgroup\TagDependencyHelper\ActiveRecordHelper;
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
 */
class ExchangeRates extends AbstractActiveRecord
{
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
            [['nbrb', 'cbr', 'created_at', 'updated_at','need_upd','is_default'], 'integer'],
            [['nbrb_rate', 'cbr_rate'], 'number'],
            [['name', 'code'], 'string', 'max' => 255]
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
                [
                    'class' => ActiveRecordHelper::className(),
                    'cache' => 'cache', // optional option - application id of cache component
                ]
            ]);
    }

    /**
     * Получаем все курсы валют, закешированы
     * @return mixed
     */
    public static function getExchangeRates()
    {
        $dep =  new TagDependency(['tags' => ActiveRecordHelper::getCommonTag(self::className()),]);
        $models = self::getDb()->cache(function ($db) {
            return ExchangeRates::find()->orderBy(['is_default' => SORT_DESC])->all($db);
        },86400,$dep);

        return $models;
    }

    /**
     * @return array
     */
    public static function getRatesCodes()
    {
        $arTmp = self::getExchangeRates();
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
}
