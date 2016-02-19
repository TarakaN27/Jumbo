<?php

namespace common\models;

use DevGroup\TagDependencyHelper\NamingHelper;
use Yii;
use yii\caching\TagDependency;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%cuser_prospects}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $description
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property CUser[] $cUsers
 */
class CuserProspects extends AbstractActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%cuser_prospects}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['name','required'],
            [['created_at', 'updated_at'], 'integer'],
            [['name', 'description'], 'string', 'max' => 255]
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
            'description' => Yii::t('app/users', 'Description'),
            'created_at' => Yii::t('app/users', 'Created At'),
            'updated_at' => Yii::t('app/users', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCUsers()
    {
        return $this->hasMany(CUser::className(), ['prospects_id' => 'id']);
    }


    /**
     * Вернем всех контрагентов
     * @return mixed
     */
    public static function getAllProspectsType()
    {
        $dep =  new TagDependency(['tags' => NamingHelper::getCommonTag(self::className())]);
        $models = self::getDb()->cache(function ($db) {
            return self::find()->all();
        },86400,$dep);

        return $models;
    }

    /**
     * вернем массив id => name
     * @return array
     */
    public static function getProspectsTypeMap()
    {
        $tmp = self::getAllProspectsType();
        return ArrayHelper::map($tmp,'id','name');
    }

}
