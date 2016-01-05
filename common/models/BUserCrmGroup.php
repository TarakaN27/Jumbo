<?php

namespace common\models;

use common\components\behavior\CacheCustomTagBehavior\CacheCustomTagBehavior;
use DevGroup\TagDependencyHelper\NamingHelper;
use Yii;
use yii\caching\DbDependency;
use yii\caching\TagDependency;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%b_user_crm_group}}".
 *
 * @property integer $id
 * @property string $name
 * @property integer $role_id
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $log_work_type
 *
 * @property BUserCrmGroup $role
 * @property BUserCrmGroup[] $bUserCrmGroups
 */
class BUserCrmGroup extends AbstractActiveRecord
{
    CONST
        LOG_WORK_TYPE_TASK = 1,
        LOG_WORK_TYPE_TIMER = 2,
        LOG_WORK_TYPE_ALL = 3;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%b_user_crm_group}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['role_id', 'created_at', 'updated_at'], 'integer'],
            [['name'], 'string', 'max' => 255],
            ['log_work_type','default','value' => self::LOG_WORK_TYPE_ALL],
            ['log_work_type','in', 'range' => array_keys(self::getLogWorkTypeArr())]
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
            'role_id' => Yii::t('app/crm', 'Role ID'),
            'created_at' => Yii::t('app/crm', 'Created At'),
            'updated_at' => Yii::t('app/crm', 'Updated At'),
            'log_work_type' => Yii::t('app/crm','Log work type')
        ];
    }

    /**
     * @return array
     */
    public static function getLogWorkTypeArr()
    {
        return [
            self::LOG_WORK_TYPE_ALL => Yii::t('app/crm','LOG WORK TYPE ALL'),
            self::LOG_WORK_TYPE_TASK => Yii::t('app/crm','LOG WORK TYPE TASK'),
            self::LOG_WORK_TYPE_TIMER => Yii::t('app/crm','LOG WORK TYPE TIMER')
        ];
    }

    /**
     * @return string
     */
    public function getLogWorkTypeStr()
    {
        $tmp = self::getLogWorkTypeArr();
        return isset($tmp[$this->log_work_type]) ? $tmp[$this->log_work_type] : 'N/A';
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRole()
    {
        return $this->hasOne(BUserCrmRoles::className(), ['id' => 'role_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBUserCrmGroups()
    {
        return $this->hasMany(BUserCrmRoles::className(), ['role_id' => 'id']);
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public static function getAllCRMGroups()
    {
        $obDep = new TagDependency([
            'tags' => NamingHelper::getCommonTag(self::className())
        ]);
        return self::getDb()->cache(function($db){
            return self::find()->all($db);
        },86400,$obDep);
    }

    /**
     * @return array
     */
    public static function getCRMGroupMap()
    {
        $tmp = self::getAllCRMGroups();
        return ArrayHelper::map($tmp,'id','name');
    }
}
