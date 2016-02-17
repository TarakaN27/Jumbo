<?php

namespace common\models;

use common\components\behavior\Contact\ContactActionBehavior;
use common\components\behavior\notifications\ContactNotificationBehavior;
use Yii;
use backend\models\BUser;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%crm_cmp_contacts}}".
 *
 * @property integer $id
 * @property integer $cmp_id
 * @property string $fio
 * @property integer $type
 * @property string $post
 * @property string $description
 * @property string $addition_info
 * @property integer $assigned_at
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $phone
 * @property string $email
 * @property integer $is_opened
 * @property integer $created_by
 * @property string $ext_id
 *
 * @property BUser $assignedAt
 * @property CUser $cmp
 * @property Dialogs[] $dialogs
 */
class CrmCmpContacts extends AbstractActiveRecord
{
    CONST
        TYPE_CLIENT  = 1,
        TYPE_PARTNER = 2,
        TYPE_OTHER = 3;

    CONST
        IS_OPENED = 1,
        IS_CLOSED = 0;

    protected
        $isConsole = FALSE;

    /**
     * @return array
     */
    public static function getOpenedClosedArr()
    {
        return [
            self::IS_OPENED => Yii::t('app/crm','Is opened'),
            self::IS_CLOSED => Yii::t('app/crm','Is closed')
        ];
    }

    /**
     * @return string
     */
    public function getOpenedClosedStr()
    {
        $tmp = self::getOpenedClosedArr();
        return isset($tmp[$this->is_opened]) ? $tmp[$this->is_opened] : 'N/A';
    }

    /**
     * @return array
     */
    public static function getTypeArr()
    {
        return [
            self::TYPE_CLIENT => Yii::t('app/crm', 'Client'),
            self::TYPE_PARTNER => Yii::t('app/crm', 'Partner'),
            self::TYPE_OTHER => Yii::t('app/crm','Other')
        ];
    }

    /**
     * @return string
     */
    public function getTypeStr()
    {
        $tmp = self::getTypeArr();
        return isset($tmp[$this->type]) ? $tmp[$this->type] : 'N/A';
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%crm_cmp_contacts}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type','fio', 'assigned_at','cmp_id'], 'required'],
            [['cmp_id', 'type', 'assigned_at', 'created_at', 'updated_at', 'is_opened','created_by'], 'integer'],
            [['description', 'addition_info','ext_id'], 'string'],
            [['fio', 'post', 'phone', 'email'], 'string', 'max' => 255],
            ['email','email']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/crm', 'ID'),
            'cmp_id' => Yii::t('app/crm', 'Cmp ID'),
            'fio' => Yii::t('app/crm', 'Fio'),
            'type' => Yii::t('app/crm', 'Type'),
            'post' => Yii::t('app/crm', 'Post'),
            'description' => Yii::t('app/crm', 'Description'),
            'addition_info' => Yii::t('app/crm', 'Addition Info'),
            'assigned_at' => Yii::t('app/crm', 'Assigned At'),
            'created_at' => Yii::t('app/crm', 'Created At'),
            'updated_at' => Yii::t('app/crm', 'Updated At'),
            'phone' => Yii::t('app/crm', 'Phone'),
            'email' => Yii::t('app/crm', 'Email'),
            'is_opened' => Yii::t('app/crm','Is opened for all'),
            'created_by' => Yii::t('app/crm','Created by')
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAssignedAt()
    {
        return $this->hasOne(BUser::className(), ['id' => 'assigned_at']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCmp()
    {
        return $this->hasOne(CUser::className(), ['id' => 'cmp_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDialogs()
    {
        return $this->hasMany(Dialogs::className(), ['crm_cmp_contact_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthor()
    {
        return $this->hasOne(BUser::className(),['id' => 'created_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFiles()
    {
        return $this->hasMany(CrmCmpFile::className(),['contact_id' => 'id']);
    }

    /**
     * @param $val
     */
    public function setIsConsole($val)
    {
        $this->isConsole = $val;
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if($this->isConsole)
            $this->created_by = 1;
        else
            $this->created_by = Yii::$app->user->id;
        return parent::beforeSave($insert);
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        $arParent = parent::behaviors();
        return ArrayHelper::merge($arParent,[
            ContactActionBehavior::className(), //сообщения
            ContactNotificationBehavior::className()    //уведомления
        ]);
    }
}
