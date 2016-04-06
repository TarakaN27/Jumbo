<?php

namespace common\models;

use backend\models\BUser;
use common\components\behavior\Company\CompanyActionBehaviors;
use common\components\behavior\notifications\CompanyNotificationBehavior;
use devgroup\TagDependencyHelper\ActiveRecordHelper;
use DevGroup\TagDependencyHelper\NamingHelper;
use Yii;
use yii\caching\DbDependency;
use yii\caching\TagDependency;
use yii\db\ActiveQuery;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use common\components\entityFields\EntityFieldsTrait;
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
 * @property integer $is_opened
 * @property integer $created_by
 * @property integer $contractor
 * @property integer $archive
 * @property integer $prospects_id
 * @property integer $allow_expense
 * @property integer $manager_crc_id
 * @property integer $source_id
 */
class CUser extends AbstractUser
{
    use \DevGroup\TagDependencyHelper\TagDependencyTrait;
    use EntityFieldsTrait; //подключаем доп настройки
    CONST
        ARCHIVE_YES = 1,
        ARCHIVE_NO = 0,
        CONTRACTOR_YES = 1,
        CONTRACTOR_NO = 0,
        IS_OPENED = 1,
        IS_CLOSED = 0,
        RESIDENT_YES = 1,
        RESIDENT_NO = 0,
        SCENARIO_REGISTER = 'register',
        SCENARIO_CHANGE_ASSIGNE = 'change_assigne';

    public
        $isNew = FALSE,
        $password;


    /**
     * @return array
     */
    public static function getArchiveArr()
    {
        return [
            self::ARCHIVE_NO => Yii::t('app/users','Contractor NO'),
            self::ARCHIVE_YES => Yii::t('app/users','Contractor YES')
        ];
    }

    /**
     * @return string
     */
    public function getArchiveStr()
    {
        $tmp = self::getArchiveArr();
        return isset($tmp[$this->archive]) ? $tmp[$this->archive] : 'N/A';
    }

    /**
     * @return array
     */
    public static function getContractorArr()
    {
        return [
            self::CONTRACTOR_NO => Yii::t('app/users','Contractor NO'),
            self::CONTRACTOR_YES => Yii::t('app/users','Contractor YES')
        ];
    }

    /**
     * @return string
     */
    public function getContractorStr()
    {
        $tmp = self::getContractorArr();
        return isset($tmp[$this->contractor]) ? $tmp[$this->contractor] : 'N/A';
    }

    /**
     * @return array
     */
    public static function getOpenedCloserArr()
    {
        return [
            self::IS_OPENED => Yii::t('app/users','Is opened'),
            self::IS_CLOSED => Yii::t('app/users','Is closed')
        ];
    }

    public function getOpenedClosedStr()
    {
        $tmp = self::getOpenedCloserArr();
        return isset($tmp[$this->is_opened]) ? $tmp[$this->is_opened] : 'N/A';
    }

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
            [[
                'role','status','created_at',
                'updated_at','manager_id','is_opened',
                'created_by','contractor','allow_expense','manager_crc_id','source_id'
            ],'integer'],

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
            ['email', 'required','except' => self::SCENARIO_CHANGE_ASSIGNE],
            ['email', 'email'],
            ['email', 'unique', 'targetClass' => self::className(),
             'message' => Yii::t('app/users','This email address has already been taken.')],
            ['email', 'string', 'max' => 255],

            ['status', 'default', 'value' => self::STATUS_BLOCKED],
            ['status', 'in', 'range' => array_keys(self::getStatusArr())],

            ['role', 'default', 'value' => self::ROLE_USER],
            ['role', 'in', 'range' => array_keys(self::getRoleArr())],

            [['type'],'integer'],
            [['type'],'required',
                'when' => function($model) {
                    if($this->allow_expense == AbstractActiveRecord::YES && $this->contractor != self::CONTRACTOR_YES)
                        return FALSE;
                    return TRUE;
                },
                'whenClient' => "function (attribute, value) {
                        var
                            allowExpense = $('#cuser-allow_expense').val(),
                            cntr = $('#cuser-contractor').val();
                        if(cntr != undefined && cntr != '".CUser::CONTRACTOR_YES."' && allowExpense != undefined && allowExpense == '".AbstractActiveRecord::YES."')
                        {
                            return false;
                        }
                        return true;
                    }",
                'except' => self::SCENARIO_CHANGE_ASSIGNE
            ],


            [['requisites_id','is_resident'],'integer'],
            ['is_resident', 'in', 'range' => array_keys(self::getResidentArr())],
            ['r_country', 'string'],

            ['is_opened','default','value' => self::IS_CLOSED],
            ['is_opened','in','range' => array_keys(self::getOpenedCloserArr())],

            ['contractor','default','value' => self::CONTRACTOR_NO],
            ['contractor','in','range' => array_keys(self::getContractorArr())],

            ['archive','default','value' => self::ARCHIVE_NO],
            ['archive','in','range' => array_keys(self::getArchiveArr())],

            [['entityFields'], 'safe'],
            ['prospects_id','required',
                'when' => function($model) {
                    if($this->allow_expense == AbstractActiveRecord::YES && $this->contractor != self::CONTRACTOR_YES)
                        return FALSE;
                    return TRUE;
                },
                'whenClient' => "function (attribute, value) {
                        var
                            allowExpense = $('#cuser-allow_expense').val(),
                            cntr = $('#cuser-contractor').val();
                        if(cntr != undefined && cntr != '".CUser::CONTRACTOR_YES."' && allowExpense != undefined && allowExpense == '".AbstractActiveRecord::YES."')
                        {
                            return false;
                        }
                        return true;
                    }",
                'except' => self::SCENARIO_CHANGE_ASSIGNE
            ],

            ['allow_expense','default','value' => AbstractActiveRecord::NO]
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
            'is_opened' => Yii::t('app/users','Is opened'),
            'created_by' => Yii::t('app/users','Created by'),
            'contractor' => Yii::t('app/users','Contractor'),
            'archive' => Yii::t('app/users','Archive'),
            'prospects_id' => Yii::t('app/users','Prospects'),
            'allow_expense' => Yii::t('app/users','Allow expense'),
            'infoWithSite' => Yii::t('app/users','Contractor'),
            'manager_crc_id' =>  Yii::t('app/users','CRC manager'),
            'statusStr' => Yii::t('app/users', 'Status'),
            'source_id' => Yii::t('app/users', 'Cuser source')
        ];
    }

    /**
     * @param bool $insert
     * @return bool|void
     */
    public function beforeSave($insert)
    {
        if($insert)
            $this->isNew = TRUE;
        return parent::beforeSave($insert);
    }

    /**
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        if($this->isNew)
            $this->createCuserSettings();

        parent::afterSave($insert, $changedAttributes);
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
     * @return ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(BUser::className(), ['id' => 'created_by']);
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
     * @return ActiveQuery
     */
    public function getExternalAccount()
    {
        return $this->hasOne(CuserExternalAccount::className(),['cuser_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getCrmContacts()
    {
        return $this->hasMany(CrmCmpContacts::className(),['cmp_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getCrmFiles()
    {
        return $this->hasMany(CrmCmpFile::className(),['cmp_id' => 'id']);
    }

    /**
     * Информация по норма часам
     * @return ActiveQuery
     */
    public function getQuantityHour()
    {
        return $this->hasOne(CuserQuantityHour::className(),['cuser_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getProspects()
    {
        return $this->hasOne(CuserProspects::className(),['id' => 'prospects_id']);
    }

    /**
     * @return $this
     */
    public function getCmpGroup()
    {
        return $this->hasMany(CUserGroups::className(), ['id' => 'group_id'])->viaTable(CuserToGroup::tableName(), ['cuser_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getManagerCrc()
    {
        return $this->hasOne(BUser::className(),['id' => 'manager_crc_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getSource()
    {
        return $this->hasOne(CuserSource::className(),['id' => 'source_id']);
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
                CompanyNotificationBehavior::className(),    //уведомления
                CompanyActionBehaviors::className()
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
                NamingHelper::getCommonTag(self::className()),
                NamingHelper::getCommonTag(CUserRequisites::className())
            ]
        ]);
        $models = self::getDb()->cache(function ($db) {
            return CUser::find()->with('requisites')->where(['contractor' => self::CONTRACTOR_YES])->notArchive()->all($db);
        },86400,$dep);

        return $models;
    }

    /**
     * вернем массив id => username
     * @return array
     */
    public static function getContractorMap()
    {
        $tmp =self::getAllContractor();
        $result = [];
        foreach($tmp as $t)
        {
            $result[$t->id] = $t->getInfo();
        }
        return $result;
    }

    /**
     * @param $iManagerID
     * @return array
     */
    public static function getContractorMapForManager($iManagerID)
    {
        $tmp = self::getContractorForManager($iManagerID);
        $result = [];
        foreach($tmp as $t)
        {
            $result[$t->id] = $t->getInfo();
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
        $obSetings = CuserSettings::findOne(['cuser_id' => $this->id]);
        if(!empty($obSetings))
            $obSetings->delete();

        if(!empty($obR))
            $obR->delete();
        return parent::afterDelete();
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
     * @return null|string
     */
    public function getInfo()
    {
        /** @var CUserRequisites $obRq */
        $obRq = $this->requisites;
        if($obRq)
            return trim($obRq->getCorpName());
        else
            return $this->username;
    }

    public function getInfoWithSite()
    {
        /** @var CUserRequisites $obRq */
        $obRq = $this->requisites;
        if($obRq)
            return trim($obRq->getCorpNameWithSite());
        else
            return $this->username;
    }

    /**
     * @param $iMngID
     * @return array
     */
    public static function getContractorForManager($iMngID)
    {
        $dep = new DbDependency(['sql' =>
            'SELECT (MAX(c.updated_at) + MAX(r.updated_at)) as control '.
            'FROM '.CUser::tableName().' c '.
            'LEFT JOIN '.CUserRequisites::tableName().' as r ON r.id = c.requisites_id '.
            'WHERE c.manager_id = '.$iMngID
        ]);
       return self::getDb()->cache(function($db) use ($iMngID){
            return self::find()
                ->with('requisites')
                ->where(['manager_id' => $iMngID,'contractor' => self::CONTRACTOR_YES])
                ->notArchive()
                ->all($db);
        },3600*24,$dep);
    }

    /**
     * @return bool
     */
    protected function createCuserSettings()
    {
        if(!CuserSettings::find()->where(['cuser_id' => $this->id])->exists())
        {
            $obSettings = new CuserSettings();
            $obSettings->cuser_id = $this->id;
            return $obSettings->save();
        }
        return FALSE;
    }

    /**
     * Получаем емаилы контрагентов по их ID
     * @param $ids
     * @return array
     */
    public static function getCUserEmails($ids)
    {
        $tmp = self::find()
            ->select([self::tableName().'.id','cur.c_email'])
            ->leftJoin(CUserRequisites::tableName().' cur','cur.id = '.self::tableName().'.requisites_id')
            ->where([self::tableName().'.id' => $ids])
            ->asArray()
            ->all();
        return ArrayHelper::map($tmp,'id','c_email');
    }


    /**
     * @return array
     * @throws \Exception
     */
    public static function getExpenseUserMap()
    {
        $dep =  new TagDependency([
            'tags' => [
                NamingHelper::getCommonTag(self::className()),
            ]
        ]);
        $models = self::getDb()->cache(function ($db) {
            return CUser::find()->with('requisites')->where(['allow_expense' => self::CONTRACTOR_YES])->notArchive()->all($db);
        },86400,$dep);

        return ArrayHelper::map($models,'id','infoWithSite');
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
    /**
     * Активные
     * @param int $state
     * @return $this
     */
    public function active($state = CUser::STATUS_ACTIVE)
    {
        return $this->andWhere([CUser::tableName().'.status' => $state]);
    }

    /**
     * Архивные
     * @return $this
     */
    public function archiveState()
    {
        return $this->andWhere([CUser::tableName().'.archive' => CUser::ARCHIVE_YES]);
    }

    /**
     * Не архивные
     * @return $this
     */
    public function notArchive()
    {
        return $this->andWhere(
            '( '.CUser::tableName().'.archive != :archive or '.CUser::tableName().'.archive IS NULL )',[
            ':archive' => CUser::ARCHIVE_YES
        ]);
    }
}
