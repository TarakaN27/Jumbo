<?php

namespace common\models;

use DevGroup\TagDependencyHelper\NamingHelper;
use Yii;
use yii\caching\TagDependency;

/**
 * This is the model class for table "{{%config}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $alias
 * @property string $value
 * @property integer $created_at
 * @property integer $updated_at
 */
class Config extends AbstractActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%config}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'alias', 'value'], 'required'],
            [['created_at', 'updated_at'], 'integer'],
            [['name', 'alias', 'value'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/common', 'ID'),
            'name' => Yii::t('app/common', 'Name'),
            'alias' => Yii::t('app/common', 'Alias'),
            'value' => Yii::t('app/common', 'Value'),
            'created_at' => Yii::t('app/common', 'Created At'),
            'updated_at' => Yii::t('app/common', 'Updated At'),
        ];
    }

    /**
     * @param int $duration
     * @return mixed
     * @throws \Exception
     */
    public static function getAll($duration = 86400)
    {
        $obDep = new TagDependency([
            'tags' => NamingHelper::getCommonTag(self::className())
        ]);

        return self::getDb()->cache(function($db){
            return self::find()->select(['name','alias','value'])->all($db);
        },$duration,$obDep);
    }
}
