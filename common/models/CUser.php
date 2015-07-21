<?php

namespace common\models;

use backend\models\BUser;
use devgroup\TagDependencyHelper\ActiveRecordHelper;
use Yii;
use yii\caching\DbDependency;
use yii\caching\TagDependency;
use yii\db\ActiveQuery;
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
 * @property string $r_country
 * @property integer $role
 * @property integer $status
 * @property integer $is_resident
 * @property integer $requisites_id
 * @property integer $created_at
 * @property integer $updated_at
 */
class CUser extends AbstractUser
{
    CONST
        RESIDENT_YES = 1,
        RESIDENT_NO = 0,
        SCENARIO_REGISTER = 'register';

    public
        $password;

    /**
     * @return array
     */
    public static function getResidentArr()
    {
        return [
            self::RESIDENT_YES => Yii::t('app/users','Resident_yes'),
            self::RESIDENT_NO => Yii::t('app/users','Resident_no'),
        ];
    }

    /**
     * @return string
     */
    public function getIsResidentStr()
    {
        $tmp = self::getResidentArr();
        return array_key_exists($this->is_resident,$tmp) ? $tmp[$this->is_resident] : 'N/A';
    }

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

            [['requisites_id','is_resident'],'integer'],
            ['is_resident', 'in', 'range' => array_keys(self::getResidentArr())],
            ['r_country', 'string'],
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
            'ext_id' => Yii::t('app/users', 'Ext ID'), //Внешний код для связки с другой CRM
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
            'is_resident' => Yii::t('app/users', 'Is resident'),
            'requisites_id' => Yii::t('app/users', 'Requisites'),
            'r_country' => Yii::t('app/users', 'Resident country'),
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
     * @return \yii\db\ActiveQuery
     */
    public function getRequisites()
    {
        return $this->hasOne(CUserRequisites::className(),['id'=>'requisites_id']);
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
        $dep =  new TagDependency([
            'tags' => [
                ActiveRecordHelper::getCommonTag(self::className()),
                ActiveRecordHelper::getCommonTag(CUserRequisites::className())
            ]
        ]);
        $models = self::getDb()->cache(function ($db) {
            return CUser::find()->with('requisites')->all($db);
        },86400,$dep);

        return $models;
    }

    /**
     * вернем массив id => username
     * @return array
     */
    public static function getContractorMap()
    {
        //$tmp = self::getAllContractor();
        //return ArrayHelper::map($tmp,'id','username');

        $tmp =self::getAllContractor();
        $result = [];
        foreach($tmp as $t)
        {
            $obR = $t->requisites;
            if(is_object($obR))
                $result[$t->id] = $t->requisites->corp_name.' '.
                    $t->requisites->j_lname.' '.
                    $t->requisites->j_fname.' '.
                    $t->requisites->j_mname;
            else
                $result[$t->id] = $t->username;
        }

        return $result;
    }




    /**
     * Устанавливаем заглушки
     */
    public function setDummyFields()
    {
        $this->username = $this->getUniqID();
        $this->email = $this->getUniqID().'@webmart.by';
    }

    /**
     * Уникальный ID
     * @return string
     */
    protected function getUniqID()
    {
        return md5(uniqid('dummy').microtime());
    }

    /**
     *
     */
    public function afterDelete()
    {
        $obR = CUserRequisites::findOne($this->requisites_id);
        if(!empty($obR))
            $obR->delete();
        return parent::afterDelete();
    }


}

/**
 * Класс для работы с запросами
 * Тут добавляем scopes
 * Class CUserQuery
 * @package common\models
 */
class CUserQuery extends ActiveQuery
{
    public function active($state = CUser::STATUS_ACTIVE)
    {
        return $this->andWhere(['status' => $state]);
    }
}
