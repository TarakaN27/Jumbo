<?php

namespace common\models;

use DevGroup\TagDependencyHelper\NamingHelper;
use Yii;
use yii\caching\TagDependency;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%partner_schemes_services_group}}".
 *
 * @property integer $id
 * @property string $name
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property PartnerSchemesServices[] $partnerSchemesServices
 * @property PartnerSchemesServicesHistory[] $partnerSchemesServicesHistories
 */
class PartnerSchemesServicesGroup extends AbstractActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%partner_schemes_services_group}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            ['name','unique'],
            ['name','string', 'max' => 255],
            [['created_at', 'updated_at'], 'integer'],
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
            'created_at' => Yii::t('app/users', 'Created At'),
            'updated_at' => Yii::t('app/users', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPartnerSchemesServices()
    {
        return $this->hasMany(PartnerSchemesServices::className(), ['group_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPartnerSchemesServicesHistories()
    {
        return $this->hasMany(PartnerSchemesServicesHistory::className(), ['group_id' => 'id']);
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public static function getGroupAll()
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
    public static function getGroupMap()
    {
        $arTmp = self::getGroupAll();
        return ArrayHelper::map($arTmp,'id','name');
    }
}
