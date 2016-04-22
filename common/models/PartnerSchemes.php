<?php

namespace common\models;

use DevGroup\TagDependencyHelper\NamingHelper;
use Yii;
use yii\caching\TagDependency;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%partner_schemes}}".
 *
 * @property integer $id
 * @property string $name
 * @property integer $start_period
 * @property integer $regular_period
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $currency_id
 *
 * @property CUser[] $cUsers
 * @property PartnerSchemesServices[] $partnerSchemesServices
 */
class PartnerSchemes extends AbstractActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%partner_schemes}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['start_period', 'regular_period', 'created_at', 'updated_at','currency_id'], 'integer'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/users', 'ID'),
            'name' => Yii::t('app/users', 'Name'),
            'start_period' => Yii::t('app/users', 'Start Period'),
            'regular_period' => Yii::t('app/users', 'Regular Period'),
            'created_at' => Yii::t('app/users', 'Created At'),
            'updated_at' => Yii::t('app/users', 'Updated At'),
            'currency_id' => Yii::t('app/users','Scheme currency')
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCUsers()
    {
        return $this->hasMany(CUser::className(), ['partner_scheme' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPartnerSchemesServices()
    {
        return $this->hasMany(PartnerSchemesServices::className(), ['scheme_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCurrency()
    {
        return $this->hasOne(ExchangeRates::className(),['id' => 'currency_id']);
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public static function getSchemesArr()
    {
        $dep = new TagDependency([
            'tags' => NamingHelper::getCommonTag(self::className())
        ]);
        return self::getDb()->cache(function($db){
            return self::find()->all();
        },86400,$dep);
    }

    /**
     * @return array
     */
    public static function getSchemesMap()
    {
        $arTmp = self::getSchemesArr();
        return ArrayHelper::map($arTmp,'id','name');
    }
}
