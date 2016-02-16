<?php

namespace common\models;

use devgroup\TagDependencyHelper\ActiveRecordHelper;
use DevGroup\TagDependencyHelper\NamingHelper;
use Yii;
use yii\caching\TagDependency;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%services}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $description
 * @property integer $status
 * @property number $rate
 * @property integer $created_at
 * @property integer $updated_at
 */
class Services extends AbstractActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%services}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['status', 'created_at', 'updated_at'], 'integer'],
            [['name'], 'string', 'max' => 255],
            ['rate','number','min' => 100],
            [['description'], 'string', 'max' => 32],
            [['name'],'unique','targetClass' => self::className(),
             'message' => Yii::t('app/services','This name has already been taken.')],
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
            'rate' => Yii::t('app/services','Rate'),
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
            ]);
    }

    /**
     * Вернем всех контрагентов
     * @return mixed
     */
    public static function getAllServices()
    {
        $dep =  new TagDependency(['tags' => NamingHelper::getCommonTag(self::className())]);
        $models = self::getDb()->cache(function ($db) {
            return Services::find()->all();
        },86400,$dep);

        return $models;
    }

    /**
     * вернем массив id => name
     * @return array
     */
    public static function getServicesMap()
    {
        $tmp = self::getAllServices();
        return ArrayHelper::map($tmp,'id','name');
    }
}
