<?php

namespace common\models;

use devgroup\TagDependencyHelper\ActiveRecordHelper;
use DevGroup\TagDependencyHelper\NamingHelper;
use Yii;
use yii\caching\TagDependency;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%legal_person}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $description
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $doc_requisites
 * @property string $doc_site
 * @property string $doc_email
 * @property integer $use_vat
 * @property integer $docx_id
 * @property integer $act_tpl_id
 * @property integer $admin_expense
 * @property integer $partner_cntr
 * @property string $address
 * @property string $telephone_number
 * @property string $ynp
 * @property string $mailing_address
 * @property integer $letter_tpl_type
 */
class LegalPerson extends AbstractActiveRecord
{
    CONST
        LETTER_TPL_TYPE_1 = 0,      //шаблон для ООО
        LETTER_TPL_TYPE_2 = 1;      //шаблон для ИП

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%legal_person}}';
    }

    /**
     * @return array
     */
    public static function getLetterTplTypeMap()
    {
        return [
            self::LETTER_TPL_TYPE_1 => Yii::t('app/services','Letter type OOO'),
            self::LETTER_TPL_TYPE_2 => Yii::t('app/services','Letter type IP')
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['description','doc_requisites','ynp','mailing_address','telephone_number'], 'string'],
            [[
                'status', 'created_at',
                'updated_at','use_vat',
                'docx_id','act_tpl_id',
                'admin_expense','partner_cntr',
                'letter_tpl_type'
            ], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['name'],'unique','targetClass' => self::className(),
             'message' => Yii::t('app/services','This name has already been taken.')],
            [['doc_site'],'url'],
            [['doc_email'],'email'],
            ['address','string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/services', 'ID'),
            'name' => Yii::t('app/services', 'Name'),
            'description' => Yii::t('app/services', 'Description'),
            'status' => Yii::t('app/services', 'Status'),
            'created_at' => Yii::t('app/services', 'Created At'),
            'updated_at' => Yii::t('app/services', 'Updated At'),
            'doc_requisites' => Yii::t('app/services','Requisites for documents'),
            'use_vat' => Yii::t('app/services', 'Use vat'),
            'docx_id' => Yii::t('app/services', 'Docx ID'),
            'act_tpl_id' => Yii::t('app/services', 'Act template'),
            'admin_expense' => Yii::t('app/services','Show expense only for admin and superadmin'),
            'partner_cntr' => Yii::t('app/services','Allow withdrawal partner percent only for contractor'),
            'address' => Yii::t('app/services','Address'),
            'ynp' => Yii::t('app/services','Ynp'),
            'mailing_address' => Yii::t('app/services','Mailing address'),
            'telephone_number' => Yii::t('app/services','Telephone number'),
            'doc_site' => Yii::t('app/services','Document site'),
            'doc_email' => Yii::t('app/services','Document email'),
            'letter_tpl_type' => Yii::t('app/services','Letter template type')
        ];
    }

    /**
     * @return mixed|null
     */
    public function getLetterTplTypeStr()
    {
        $tmp = self::getLetterTplTypeMap();
        return isset($tmp[$this->letter_tpl_type]) ? $tmp[$this->letter_tpl_type] : NULL;
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

            ]);
    }

    /**
     * Вернем всех контрагентов
     * @return mixed
     */
    public static function getAllLegalPerson()
    {
        $dep =  new TagDependency(['tags' => NamingHelper::getCommonTag(self::className())]);
        $models = self::getDb()->cache(function ($db) {
            return LegalPerson::find()->orderBy(['id' => SORT_ASC])->all($db);
        },86400,$dep);

        return $models;
    }

    /**
     * вернем массив id => name
     * @return array
     */
    public static function getLegalPersonMap()
    {
        $tmp = self::getAllLegalPerson();
        return ArrayHelper::map($tmp,'id','name');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDocx()
    {
        return $this->hasOne(BillDocxTemplate::className(),['id'=>'docx_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getActTemplate()
    {
        return $this->hasOne(ActsTemplate::className(),['id' => 'act_tpl_id']);
    }

    /**
     * @throws \Exception
     */
    public static function getLegalPersonMapWithRoleControl()
    {
        $dep =  new TagDependency(['tags' => NamingHelper::getCommonTag(self::className())]);
        if(Yii::$app->user->can('adminRights'))
            $models = self::getDb()->cache(function ($db) {
                return LegalPerson::find()->orderBy(['id' => SORT_ASC])->all($db);
            },86400,$dep);
        else
            $models = self::getDb()->cache(function ($db) {
                return LegalPerson::find()->where('admin_expense is NULL or admin_expense = 0')->orderBy(['id' => SORT_ASC])->all($db);
            },86400,$dep);

        return ArrayHelper::map($models,'id','name');
    }
}
