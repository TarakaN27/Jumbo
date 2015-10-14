<?php

namespace common\models;

use devgroup\TagDependencyHelper\ActiveRecordHelper;
use DevGroup\TagDependencyHelper\NamingHelper;
use Yii;
use yii\caching\TagDependency;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%cuser_types}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $description
 * @property integer $created_at
 * @property integer $updated_at
 */
class CUserTypes extends AbstractActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%cuser_types}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'],'unique','targetClass' => self::className(),
             'message' => Yii::t('app/users','This name has already been taken.')],
            [['created_at', 'updated_at'], 'integer'],
            [['description'], 'string'],
            [['name'], 'string', 'max' => 128]
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
     * Вернем все типы контрагентов
     * @return mixed
     */
    public static function getAllUserTypes()
    {
        $dep =  new TagDependency(['tags' => NamingHelper::getCommonTag(self::className()),]);
        $models = self::getDb()->cache(function ($db) {
            return CUserTypes::find()->all($db);
        },186400,$dep);

        return $models;
    }

    /**
     * вернем массив id => username
     * @return array
     */
    public static function getUserTypesMap()
    {
        $tmp = self::getAllUserTypes();
        return ArrayHelper::map($tmp,'id','name');
    }
}
