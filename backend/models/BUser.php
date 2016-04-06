<?php

namespace backend\models;

use app\models\BindBuser;
use common\components\behavior\notifications\BUserNotificationsBehavior;
use common\models\AbstractUser;
use common\models\BUserCrmGroup;
use common\models\BUserCrmRules;
use DevGroup\TagDependencyHelper\NamingHelper;
use Yii;
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
 * @property integer $crm_group_id
 * @property string $fname
 * @property string $lname
 * @property string $mname
 * @property integer $log_work_type
 * @property integer $allow_unit
 */
class BUser extends AbstractUser
{
    use \DevGroup\TagDependencyHelper\TagDependencyTrait; //Трейд добавляет функционал для кешировани по тегам
    /**
     * описываем роли пользователей backend
     */
    CONST
        ROLE_E_MARKETER = 6,
        ROLE_JURIST = 7,
        ROLE_MANAGER = 10,
        ROLE_BOOKKEEPER = 15,
        ROLE_ADMIN = 20,
        ROLE_SUPERADMIN = 25,
        SCENARIO_CHANGE_PASSWORD = 'change_password',
        SCENARIO_REGISTER = 'register';

    public
        $password;

    CONST
        LOG_WORK_TYPE_DEFAULT = 0,
        LOG_WORK_TYPE_TASK = 1,
        LOG_WORK_TYPE_TIMER = 2;

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
            self::ROLE_SUPERADMIN => Yii::t('app/users','USER_role_superadmin'),
            self::ROLE_JURIST => Yii::t('app/users','USER_role_jurist'),
            self::ROLE_E_MARKETER => Yii::t('app/users','USER_role_e_marketer')
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
            [['role','status','created_at','updated_at','crm_group_id'],'integer'],
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

            ['log_work_type','default','value' => self::LOG_WORK_TYPE_DEFAULT],
            ['log_work_type','in', 'range' => array_keys(self::getLogWorkTypeArr())],

            ['allow_unit','integer'],
            ['allow_unit','default','value' => 0]
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
            'mname' => Yii::t('app/users', 'Midle name'),
            'crm_group_id' => Yii::t('app/users', 'Crm Group Id'),
            'log_work_type' => Yii::t('app/crm','Log work type'),
            'allow_unit' => Yii::t('app/users','Allow unit'),
            'roleStr' => Yii::t('app/users','Role Str'),
            'fio' => Yii::t('app/users','Manager')
        ];
    }


    /**
     * @return array
     */
    public static function getLogWorkTypeArr()
    {
        return [
            self::LOG_WORK_TYPE_DEFAULT => Yii::t('app/crm','LOG WORK TYPE DEFAULT'),
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
        $dependency = new TagDependency(['tags' => NamingHelper::getCommonTag(self::className()),]);
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
        $dep =  new TagDependency(['tags' => NamingHelper::getCommonTag(self::className()),]);
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

        $arReturn = [];
        if(is_array($tmp))
            foreach($tmp as $t)
                $arReturn[$t->id] = $t->getFio();

        return $arReturn;
    }

    /**
     * @return string
     */
    public function getFio()
    {
        $str = trim($this->lname.' '.$this->fname.' '.$this->mname);
        return empty($str) ? $this->username : $str;
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
                'CacheableActiveRecord' => [
                    'class' => \DevGroup\TagDependencyHelper\CacheableActiveRecord::className(),
                ],
                BUserNotificationsBehavior::className()     //уведомления
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
     * @return \yii\db\ActiveQuery
     */
    public function getCRMGroup()
    {
        return $this->hasOne(BUserCrmGroup::className(),['id' => 'crm_group_id']);
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

    /**
     * Получаем администратора(используется для отправки технических сообщений)
     * @return mixed
     * @throws \Exception
     */
    public static function getAdmin()
    {
        $obDep = new TagDependency([
                'tags' => NamingHelper::getCommonTag(self::className())
        ]);

        return self::getDb()->cache(function(){
            return self::find()
                ->where(['role' => [self::ROLE_ADMIN,self::ROLE_SUPERADMIN]])
                ->orderBy(['role' => SORT_DESC,'id' => SORT_ASC])
                ->limit(1)
                ->one();
        },86400,$obDep);
    }

    /**
     * @param $model_id
     * @return mixed
     */
    public static function findOneByIDCached($model_id)
    {
        $obDep = new TagDependency([
            'tags' => [
                NamingHelper::getObjectTag(self::className(),$model_id),
            ]
        ]);
        return self::getDb()->cache(function() use ($model_id){
            return self::findOne($model_id);
        },3600*24,$obDep);
    }

    /**
     * @param $entity
     * @return array|bool
     */
    public function getCRMRulesByGroup($entity)
    {
        if(empty($this->crm_group_id))
            return [];

        $data =  BUserCrmRules::find()
            ->leftJoin(BUserCrmGroup::tableName().' as groups ',BUserCrmRules::tableName().'.role_id = groups.role_id')
            ->where([
                'groups.id' => $this->crm_group_id,
                'entity' => $entity
            ])
            ->one();

        return $data;
    }

    /**
     * @param $ID
     * @return mixed
     * @throws \Exception
     */
    public static function findOneByIdCachedForSelect2($ID)
    {
        $obDep = new TagDependency([
            'tags' => [
                NamingHelper::getObjectTag(self::className(),$ID),
            ]
        ]);
        return self::getDb()->cache(function() use ($ID){
            $obManCrc = BUser::find()->select(['id','fname','lname','mname'])->where(['id' => $ID])->one();
            return $obManCrc ? $obManCrc->getFio() : NULL;
        },86400,$obDep);
    }
}
