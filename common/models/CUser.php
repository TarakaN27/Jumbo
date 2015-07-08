<?php

namespace common\models;

use backend\models\BUser;
use devgroup\TagDependencyHelper\ActiveRecordHelper;
use Yii;
use yii\caching\DbDependency;
use yii\caching\TagDependency;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%c_user}}".
 *
 * @property integer $id
 * @property string $username
 * @property integer $ext_id
 * @property integer $type
 * @property integer $manager_id
 * @property string $auth_key
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $email
 * @property integer $role
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 */
class CUser extends AbstractUser
{
    CONST
        SCENARIO_REGISTER = 'register';

    public
        $password;

    /**
     * @return array
     */
    public static function getRoleArr()
    {
        return [
            self::ROLE_USER => Yii::t('app/users','USER_role_user')
        ];
    }

    /**
     * @return string
     */
    public function getRoleStr()
    {
        $arrRole = self::getRoleStr();
        return isset($arrRole[$this->role]) ? $arrRole[$this->role] : 'N/A';
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%c_user}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['email'],'required'],
            [['role','status','created_at','updated_at','manager_id'],'integer'],
            [['password_hash','password_reset_token','email'],'string', 'max' => 255],
            [['auth_key'], 'string', 'max' => 32],
            //имя пользователя
            ['username', 'required'],
            ['username', 'match', 'pattern' => '#^[\w_-]+$#i'],
            ['username', 'unique', 'targetClass' => self::className(),
             'message' => Yii::t('app/users','This username has already been taken.')],
            ['username', 'string', 'min' => 2, 'max' => 255],

            ['ext_id', 'string'],
            ['ext_id','filter', 'filter' => 'trim', 'skipOnArray' => true],
            ['ext_id', 'unique', 'targetClass' => self::className(),
             'message' => Yii::t('app/users','This ext_id has already been taken.')],

            //емаил
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'unique', 'targetClass' => self::className(),
             'message' => Yii::t('app/users','This email address has already been taken.')],
            ['email', 'string', 'max' => 255],

            ['status', 'default', 'value' => self::STATUS_BLOCKED],
            ['status', 'in', 'range' => array_keys(self::getStatusArr())],

            ['role', 'default', 'value' => self::ROLE_USER],
            ['role', 'in', 'range' => array_keys(self::getRoleArr())],

            [['type'],'required'],
            [['type'],'integer'],
            //['type', 'default', 'value' => self::TYPE_U_PERSON],
            //['type', 'in', 'range' => array_keys(self::getTypeArr())],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/users', 'ID'),
            'username' => Yii::t('app/users', 'Username'),
            'ext_id' => Yii::t('app/users', 'Ext ID'),
            'type' => Yii::t('app/users', 'Type'),
            'manager_id' => Yii::t('app/users', 'Manager ID'),
            'auth_key' => Yii::t('app/users', 'Auth Key'),
            'password_hash' => Yii::t('app/users', 'Password Hash'),
            'password_reset_token' => Yii::t('app/users', 'Password Reset Token'),
            'email' => Yii::t('app/users', 'Email'),
            'role' => Yii::t('app/users', 'Role'),
            'status' => Yii::t('app/users', 'Status'),
            'created_at' => Yii::t('app/users', 'Created At'),
            'updated_at' => Yii::t('app/users', 'Updated At'),
            'password' => Yii::t('app/users', 'Password'),
        ];
    }

    /**
     * @param bool $insert
     * @return bool|void
     */
    public function beforeSave($insert)
    {
        return parent::beforeSave($insert);
    }

    /**
     * @return static
     * возвращаем менеджеров для клиента
     */
    public function getManager()
    {
        return $this->hasOne(BUser::className(), ['id' => 'manager_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserType()
    {
        return $this->hasOne(CUserTypes::className(), ['id' => 'type']);
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
    public static function getAllContractor()
    {
        $dep =  new TagDependency(['tags' => ActiveRecordHelper::getCommonTag(self::className()),]);
        $models = self::getDb()->cache(function ($db) {
            return CUser::find()->all($db);
        },86400,$dep);

        return $models;
    }

    /**
     * вернем массив id => username
     * @return array
     */
    public static function getContractorMap()
    {
        $tmp = self::getAllContractor();
        return ArrayHelper::map($tmp,'id','username');
    }
}
