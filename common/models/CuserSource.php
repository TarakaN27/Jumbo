<?php

namespace common\models;

use DevGroup\TagDependencyHelper\NamingHelper;
use Yii;
use yii\caching\TagDependency;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%cuser_source}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $description
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property CUser[] $cUsers
 */
class CuserSource extends AbstractActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%cuser_source}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'],'required'],
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
        return $this->hasMany(CUser::className(), ['source_id' => 'id']);
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public static function getAllSource()
    {
        $obDep = new TagDependency([
            'tags' => NamingHelper::getCommonTag(self::className())
        ]);

        return self::getDb()->cache(function(){
            return self::find()->select(['id','name','description'])->all();
        },86400,$obDep);
    }

    /**
     * @return array
     */
    public static function getSourceMap()
    {
        return ArrayHelper::map(self::getAllSource(),'id','name');
    }
}
