<?php

namespace backend\models;

use common\models\AbstractUser;
use Yii;
use yii\caching\DbDependency;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%b_user}}".
 *
 * @property integer $id
 * @property string $username
 * @property string $auth_key
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $email
 * @property integer $role
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $fname
 * @property string $lname
 * @property string $mname
 */
class BUser extends AbstractUser
{
    /**
     * описываем роли пользователей backend
     */
    CONST
        ROLE_MANAGER = 10,
        ROLE_BOOKKEEPER = 15,
        ROLE_ADMIN = 20,
        ROLE_SUPERADMIN = 25,
        SCENARIO_CHANGE_PASSWORD = 'change_password',
        SCENARIO_REGISTER = 'register';

    public
        $password;

    /**
     * вернем массив со всеми ролями
     * @return array
     */
    public static function getRoleArr()
    {
        return [
            self::ROLE_USER => Yii::t('app/users','USER_role_user'),
            self::ROLE_MANAGER => Yii::t('app/users','USER_role_manager'),
            self::ROLE_BOOKKEEPER => Yii::t('app/users','USER_role_bookkeeper'),
            self::ROLE_ADMIN => Yii::t('app/users','USER_role_admin'),
            self::ROLE_SUPERADMIN => Yii::t('app/users','USER_role_superadmin')
        ];
    }

    /**
     * вернем роль строкой
     * @return string
     */
    public function getRoleStr()
    {
        $arRole = self::getRoleArr();
        return isset($arRole[$this->role]) ? $arRole[$this->role] : 'N/A';
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%b_user}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['email'],'required'],
            [['role','status','created_at','updated_at'],'integer'],
            [['password_hash','password_reset_token','email'],'string', 'max' => 255],
            [['auth_key'], 'string', 'max' => 32],
            //имя пользователя
            ['username', 'required'],
            ['username', 'match', 'pattern' => '#^[\w_-]+$#i'],
            ['username', 'unique', 'targetClass' => self::className(),
             'message' => Yii::t('app','This username has already been taken.')],
            ['username', 'string', 'min' => 2, 'max' => 255],
            //емаил
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'unique', 'targetClass' => self::className(),
             'message' => Yii::t('app','This email address has already been taken.')],
            ['email', 'string', 'max' => 255],

            ['status', 'default', 'value' => self::STATUS_BLOCKED],
            ['status', 'in', 'range' => array_keys(self::getStatusArr())],

            ['role', 'default', 'value' => self::ROLE_USER],
            ['role', 'in', 'range' => array_keys(self::getRoleArr())],

            ['password', 'required','on'=>[self::SCENARIO_REGISTER]],
            ['password', 'string', 'min' => 6],

            [['fname','lname','mname'], 'string', 'min' => 2, 'max' => 255],

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
            'auth_key' => Yii::t('app/users', 'Auth Key'),
            'password_hash' => Yii::t('app/users', 'Password Hash'),
            'password_reset_token' => Yii::t('app/users', 'Password Reset Token'),
            'email' => Yii::t('app/users', 'Email'),
            'role' => Yii::t('app/users', 'Role'),
            'status' => Yii::t('app/users', 'Status'),
            'created_at' => Yii::t('app/users', 'Created At'),
            'updated_at' => Yii::t('app/users', 'Updated At'),
            'password' => Yii::t('app/users', 'Password'),
            'fname' => Yii::t('app/users', 'First name'),
            'lname' => Yii::t('app/users', 'Last name'),
            'mname' => Yii::t('app/users', 'Midle name')
        ];
    }

    /**
     * @param bool $insert
     * @return bool|void
     */
    public function beforeSave($insert)
    {
        if($insert && $this->scenario == self::SCENARIO_REGISTER)
        {
            $this->setPassword($this->password);
        }
        return parent::beforeSave($insert);
    }

    /**
     * @return array
     */
    public static function getListManagers()
    {
        $dependency = new DbDependency(['sql' => 'SELECT MAX(updated_at) FROM '.self::tableName().' WHERE role = '.self::ROLE_MANAGER]);
        $arMng = self::getDb()->cache(function ($db) {
            return self::find()->select(['id','username'])->where(['role' => self::ROLE_MANAGER])->all();
        }, 3600*24, $dependency);
        if(empty($arMng))
            return [];
        else
            return ArrayHelper::map($arMng,'id','username');
    }

    /**
     * @return string
     */
    public function getFio()
    {
        return $this->lname.' '.$this->fname.' '.$this->mname;
    }
}
