<?php

namespace common\models;

use DevGroup\TagDependencyHelper\NamingHelper;
use Yii;
use yii\caching\TagDependency;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%b_user_crm_roles}}".
 *
 * @property integer $id
 * @property string $name
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property BUserCrmRules[] $bUserCrmRules
 */
class BUserCrmRoles extends AbstractActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%b_user_crm_roles}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['created_at', 'updated_at'], 'integer'],
            [['name'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/crm', 'ID'),
            'name' => Yii::t('app/crm', 'Name'),
            'created_at' => Yii::t('app/crm', 'Created At'),
            'updated_at' => Yii::t('app/crm', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBUserCrmRules()
    {
        return $this->hasMany(BUserCrmRules::className(), ['role_id' => 'id']);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public static function getRoleMap()
    {
        $obDep = new TagDependency([
            'tags' => NamingHelper::getCommonTag(self::className())
        ]);
        $data = self::getDb()->cache(function($db){
            return self::find()->select(['id','name'])->all($db);
        },86400,$obDep);
        return ArrayHelper::map($data,'id','name');
    }
}
