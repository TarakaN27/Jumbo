<?php

namespace common\models;

use devgroup\TagDependencyHelper\ActiveRecordHelper;
use Yii;
use yii\caching\TagDependency;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%legal_person}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $description
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 */
class LegalPerson extends AbstractActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%legal_person}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['description'], 'string'],
            [['status', 'created_at', 'updated_at'], 'integer'],
            [['name'], 'string', 'max' => 255]
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
            'description' => Yii::t('app/services', 'Description'),
            'status' => Yii::t('app/services', 'Status'),
            'created_at' => Yii::t('app/services', 'Created At'),
            'updated_at' => Yii::t('app/services', 'Updated At'),
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
     * Вернем всех контрагентов
     * @return mixed
     */
    public static function getAllLegalPerson()
    {
        $dep =  new TagDependency(['tags' => ActiveRecordHelper::getCommonTag(self::className())]);
        $models = self::getDb()->cache(function ($db) {
            return LegalPerson::find()->orderBy(['id' => SORT_ASC])->all($db);
        },86400,$dep);

        return $models;
    }

    /**
     * вернем массив id => name
     * @return array
     */
    public static function getLegalPersonMap()
    {
        $tmp = self::getAllLegalPerson();
        return ArrayHelper::map($tmp,'id','name');
    }
}
