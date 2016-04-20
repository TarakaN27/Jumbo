<?php

namespace common\models;

use backend\models\BUser;
use DevGroup\TagDependencyHelper\NamingHelper;
use Yii;
use yii\caching\TagDependency;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * This is the model class for table "{{%bonus_scheme}}".
 *
 * @property integer $id
 * @property string $name
 * @property integer $type
 * @property integer $num_month
 * @property integer $inactivity
 * @property integer $grouping_type
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $payment_base
 */
class BonusScheme extends AbstractActiveRecord
{
    CONST
        BASE_PAYMENT = 0,           //counting bonus by each payment
        BASE_SALE = 1,              //counting bonus by each sale
        TYPE_UNITS = 1,             //тип бонусной схемы unit
        TYPE_SIMPLE_BONUS =2,       //тип бонусной схемы бонусы за продажи(для аккаунтеров)
        TYPE_COMPLEX_TYPE = 3,      //комплексный тип (для АНТОНА!)
        TYPE_COMPLEX_PARTNER = 4,   //комплексный тип для менеджеров партнеров
        GROUP_BY_COMPANY = 1,       //группировка платежей по одной компании
        GROUP_BY_CMP_GROUP =2;      //группировка платежей по группе компаний

    /**
     * @return array
     */
    public static function getPaymentBaseArr()
    {
        return [
            self::BASE_PAYMENT => Yii::t('app/bonus','Base payment'),
            self::BASE_SALE => Yii::t('app/bonus','Base sale')
        ];
    }

    /**
     * @return mixed|null
     */
    public function getPaymentBaseStr()
    {
        $tmp = self::getPaymentBaseArr();
        return isset($tmp[$this->payment_base]) ? $tmp[$this->payment_base] : NULL;
    }

    /**
     * @return array
     */
    public static function getGroupByMap()
    {
        return [
            self::GROUP_BY_COMPANY => Yii::t('app/bonus','Group by company'),
            self::GROUP_BY_CMP_GROUP => Yii::t('app/bonus','Group by company group')
        ];
    }

    /**
     * @return string
     */
    public function getGroupingTypeStr()
    {
        $tmp = self::getGroupByMap();
        return isset($tmp[$this->grouping_type]) ? $tmp[$this->grouping_type] : NULL;
    }

    /**
     * @return array
     */
    public static function getTypeMap()
    {
        return [
            self::TYPE_UNITS => Yii::t('app/bonus','Type units'),
            self::TYPE_SIMPLE_BONUS => Yii::t('app/bonus','Type simple bonus'),
            self::TYPE_COMPLEX_TYPE => Yii::t('app/bonus','Type complex'),
            self::TYPE_COMPLEX_PARTNER => Yii::t('app/bonus','Type complex partner'),
        ];

    }

    /**
     * @return string
     */
    public function getTypeStr()
    {
        $tmp = self::getTypeMap();
        return isset($tmp[$this->type]) ? $tmp[$this->type] : 'N/A';
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%bonus_scheme}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name','type'],'required'],
            [[
                'type', 'num_month',
                'grouping_type', 'created_at', 'updated_at',
                'infinite','payment_base'
            ], 'integer'],
            [['name'], 'string', 'max' => 255],
            ['grouping_type','default','value' => self::GROUP_BY_COMPANY],
            ['name','unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/bonus', 'ID'),
            'name' => Yii::t('app/bonus', 'Name'),
            'type' => Yii::t('app/bonus', 'Type'),
            'num_month' => Yii::t('app/bonus', 'Num Month'),
            'infinite' => Yii::t('app/bonus','Infinite'),
            'grouping_type' => Yii::t('app/bonus', 'Grouping Type'),
            'created_at' => Yii::t('app/bonus', 'Created At'),
            'updated_at' => Yii::t('app/bonus', 'Updated At'),
            'payment_base' => Yii::t('app/bonus', 'Payment base')
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getServices()
    {
        return $this->hasMany(BonusSchemeService::className(),['scheme_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsersID()
    {
        return $this->hasMany(BonusSchemeToBuser::className(),['scheme_id' => 'id']);
    }

    /**
     * @return $this
     */
    public function getUsers()
    {
        return $this->hasMany(BUser::className(),['id' => 'buser_id'])->viaTable(BonusSchemeToBuser::tableName(),['scheme_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCuserID()
    {
        return $this->hasMany(BonusSchemeToCuser::className(),['scheme_id' => 'id']);
    }

    /**
     * @return $this
     */
    public function getCusers()
    {
        return $this->hasMany(CUser::className(),['id' => 'cuser_id'])->viaTable(BonusSchemeToCuser::tableName(),['scheme_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getExceptCuserID()
    {
        return $this->hasMany(BonusSchemeExceptCuser::className(),['scheme_id' => 'id']);
    }

    /**
     * @return $this
     */
    public function getExceptCusers()
    {
        return $this->hasMany(CUser::className(),['id' => 'cuser_id'])->viaTable(BonusSchemeExceptCuser::tableName(),['scheme_id' => 'id']);
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public static function getAllBonusScheme()
    {
        $dep = new TagDependency([
            'tags' => NamingHelper::getCommonTag(self::className())
        ]);

        return self::getDb()->cache(function(){
            return self::find()->all();
        },86400,$dep);
    }

    /**
     * @return array
     */
    public static function getBonusSchemeMap()
    {
        return ArrayHelper::map(self::getAllBonusScheme(),'id','name');
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    protected static function getSchemesForNotAdminUser()
    {
        $dep = new TagDependency([
            'tags' => NamingHelper::getCommonTag(self::className())
        ]);
        return self::getDb()->cache(function(){
            $arScheme1 = self::find()   //выбираем схемы только для пользователя
            ->select(['name',self::tableName().'.id','type'])
                ->joinWith('usersID')
                ->where([
                    BonusSchemeToBuser::tableName().'.buser_id' => Yii::$app->user->id
                ])
                ->all();
            return $arScheme1;
        },86400,$dep);
    }

    /**
     * @return array
     */
    public static function getBonusSchemeByRights()
    {
        if(Yii::$app->user->can('adminRights')) //для админов и суперадминов доступы все схемы
        {
            return self::getBonusSchemeMap();
        }
        $tmp = self::getSchemesForNotAdminUser();
        return array_unique(ArrayHelper::map($tmp,'id','name'));
    }

    /**
     * @return array
     */
    public static function getBonusSchemeTypeMapByRights()
    {
        if(Yii::$app->user->can('adminRights')) //для админов и суперадминов доступы все схемы
        {
            return self::getTypeMap();
        }
        $tmp = self::getSchemesForNotAdminUser();
        return array_unique(ArrayHelper::map($tmp,'type','typeStr'));
    }
}
