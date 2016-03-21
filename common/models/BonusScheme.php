<?php

namespace common\models;

use Yii;

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
        TYPE_BONUS =2,          //тип бонусной схемы бонусы за продажи
        GROUP_BY_COMPANY = 1,   //группировка платежей по одной компании
        GROUP_BY_CMP_GROUP =2;  //группировка платежей по группе компаний

    /**
     * @return array
     */
    public static function getGroupByMap()
    {
        return [
            self::GROUP_BY_COMPANY => Yii::t('app/users','Group by company'),
            self::GROUP_BY_CMP_GROUP => Yii::t('app/users','Group by company group')
        ];
    }

    public function getGroupingTypeStr()
    {
        $tmp = self::getGroupByMap();
        return isset($tmp[$this->grouping_type]) ? $tmp[$this->grouping_type] : 'N/A';
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
            [[
                'type', 'num_month', 'inactivity',
                'grouping_type', 'created_at', 'updated_at'
            ], 'integer'],
            [['name'], 'string', 'max' => 255],
            ['grouping_type','default','value' => self::GROUP_BY_COMPANY]
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
            'type' => Yii::t('app/users', 'Type'),
            'num_month' => Yii::t('app/users', 'Num Month'),
            'inactivity' => Yii::t('app/users', 'Inactivity'),
            'grouping_type' => Yii::t('app/users', 'Grouping Type'),
            'created_at' => Yii::t('app/users', 'Created At'),
            'updated_at' => Yii::t('app/users', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getServices()
    {
        return $this->hasMany(BonusSchemeService::className(),['scheme_id' => 'id']);
    }
}
