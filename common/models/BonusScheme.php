<?php

namespace common\models;

use backend\models\BUser;
use Yii;
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
 */
class BonusScheme extends AbstractActiveRecord
{
    CONST
        TYPE_UNITS = 1,         //тип бонусной схемы unit
        TYPE_SIMPLE_BONUS =2,   //тип бонусной схемы бонусы за продажи(для аккаунтеров)
        TYPE_COMPLEX_TYPE = 3,  //комплексный тип (для АНТОНА!)
        GROUP_BY_COMPANY = 1,   //группировка платежей по одной компании
        GROUP_BY_CMP_GROUP =2;  //группировка платежей по группе компаний

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
        return isset($tmp[$this->grouping_type]) ? $tmp[$this->grouping_type] : 'N/A';
    }

    /**
     * @return array
     */
    public static function getTypeMap()
    {
        return [
            self::TYPE_UNITS => Yii::t('app/bonus','Type units'),
            self::TYPE_SIMPLE_BONUS => Yii::t('app/bonus','Type simple bonus'),
            self::TYPE_COMPLEX_TYPE => Yii::t('app/bonus','Type complex')
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
                'infinite'
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
}
