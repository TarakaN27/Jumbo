<?php

namespace backend\models;

use app\models\BindBuser;
use common\models\AbstractUser;
use devgroup\TagDependencyHelper\ActiveRecordHelper;
use Yii;
use yii\caching\DbDependency;
use yii\caching\TagDependency;
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
     * @return array
     */
    public static function getRoleArrWithRights()
    {
        $tmp = self::getRoleArr();
        if(!Yii::$app->user->can('superRights')) //только супер админ может создавать админов
        {
            if(isset($tmp[self::ROLE_ADMIN]))
                unset($tmp[self::ROLE_ADMIN]);

            if(isset($tmp[self::ROLE_SUPERADMIN]))
                unset($tmp[self::ROLE_SUPERADMIN]);
        }

        return $tmp;
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
     * @return mixed
     */
    public static function getManagersArr()
    {
        $dependency = new TagDependency(['tags' => ActiveRecordHelper::getCommonTag(self::className()),]);
        return self::getDb()->cache(function ($db) {
            return self::find()->select(['id','username'])->where(['role' => self::ROLE_MANAGER])->all($db);
        }, 3600*24, $dependency);
    }

    /**
     * @return array
     */
    public static function getListManagers()
    {
        $arMng = self::getManagersArr();
        if(empty($arMng))
            return [];
        else
            return ArrayHelper::map($arMng,'id','username');
    }

    /**
     * Get all members. Return full activeRecord Objects.
     * @return mixed
     */
    public static function getAllMembersObj()
    {
        $dep =  new TagDependency(['tags' => ActiveRecordHelper::getCommonTag(self::className()),]);
        $models = self::getDb()->cache(function ($db) {
            return BUser::find()->all($db);
        },86400,$dep);

        return $models;
    }

    /**
     * Get map of all members
     * @param null $exeptID
     * @return array
     */
    public static function getAllMembersMap($exeptID = NULL)
    {
        $tmp = self::getAllMembersObj();
        if(is_array($tmp) && !is_null($exeptID))
            foreach($tmp as $key => $item)
                if($item->id == $exeptID)
                    unset($tmp[$key]);

        return !is_array($tmp) ? [] : ArrayHelper::map($tmp,'id','username');
    }

    /**
     * @return string
     */
    public function getFio()
    {
        return $this->lname.' '.$this->fname.' '.$this->mname;
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
     * @return static
     */
    public function getBindMembers()
    {
        return $this->hasMany(self::className(), ['id' => 'member_id'])->viaTable(BindBuser::tableName(), ['buser_id' => 'id']);
    }

    /**
     * @return array
     */
    public static function getRoleByPermission()
    {
        if(Yii::$app->user->isGuest)
            return [''];

        switch(Yii::$app->user->identity->role)
        {
            case self::ROLE_SUPERADMIN:
                $arRole = self::getRoleArr();
                break;
            case self::ROLE_ADMIN:
                $arRole = self::getRoleArr();
                if(array_key_exists(self::ROLE_SUPERADMIN,$arRole))
                    unset($arRole[self::ROLE_SUPERADMIN]);
                break;
            case self::ROLE_MANAGER:
            case self::ROLE_BOOKKEEPER:
                $arRole = self::getRoleArr();
                if(array_key_exists(self::ROLE_SUPERADMIN,$arRole))
                    unset($arRole[self::ROLE_SUPERADMIN]);
                if(array_key_exists(self::ROLE_ADMIN,$arRole))
                    unset($arRole[self::ROLE_ADMIN]);
                break;
            default:
                $arRole = [''];
                break;
        }

        return $arRole;
    }
}
