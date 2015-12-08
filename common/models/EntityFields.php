<?php

namespace common\models;

use common\components\behavior\CacheCustomTagBehavior\CacheCustomTagBehavior;
use common\components\helpers\CustomHelper;
use Yii;
use yii\caching\TagDependency;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%entity_fields}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $alias
 * @property string $entity
 * @property integer $type
 * @property integer $required
 * @property integer $validate
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property EntityFieldsValue[] $entityFieldsValues
 */
class EntityFields extends AbstractActiveRecord
{
    CONST
        TYPE_TEXT = 1,
        TYPE_CHECKBOX = 2,
        TYPE_TEXTAREA = 3,
        TYPE_DROPDOWN = 4,
        TYPE_DATE = 5;

    CONST
        VALID_EMAIL = 1,
        VALID_PHONE = 2;

    /**
     * @return array
     */
    public static function getValidArr()
    {
        return [
            self::VALID_EMAIL => Yii::t('app/crm','Email'),
            self::VALID_PHONE => Yii::t('app/crm','Phone')
        ];
    }

    /**
     * @return string
     */
    public function getValidateStr()
    {
        $tmp = self::getValidArr();
        return isset($tmp[$this->validate]) ? $tmp[$this->validate] : 'N/A';
    }

    /**
     * @return array
     */
    public static function getTypeArr()
    {
        return [
            self::TYPE_TEXT => Yii::t('app/crm','Text'),
            self::TYPE_CHECKBOX => Yii::t('app/crm','Checkbox'),
            self::TYPE_TEXTAREA => Yii::t('app/crm','Textarea'),
            self::TYPE_DROPDOWN => Yii::t('app/crm','Dropdown'),
            self::TYPE_DATE => Yii::t('app/crm','Email')
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
     * @return string
     */
    public function getRequiredStr()
    {
        return self::getYesNoStr($this->required);
    }

    /**
     * Возращает список сущностей для которых доступны доп поля
     * @todo возможно стоит сделать настройку в модели, получать список моделей из заданных папок и включать нужные?
     * @return array
     */
    public static function getEntityArr()
    {
        return[
            Acts::getModelName() => Yii::t('app/crm','Акты'),
        ];
    }

    /**
     * @return string
     */
    public function getEntityStr()
    {
        $tmp = self::getEntityArr();
        return isset($tmp[$this->entity]) ? $tmp[$this->entity] : 'N/A';
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%entity_fields}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'alias', 'entity'], 'required'],
            [['type', 'required', 'validate', 'created_at', 'updated_at'], 'integer'],
            [['name', 'alias', 'entity'], 'string', 'max' => 255],
            [['name'], 'unique'],
            [['alias'], 'unique']
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
            'alias' => Yii::t('app/crm', 'Alias'),
            'entity' => Yii::t('app/crm', 'Entity'),
            'type' => Yii::t('app/crm', 'Type'),
            'required' => Yii::t('app/crm', 'Required'),
            'validate' => Yii::t('app/crm', 'Validate'),
            'created_at' => Yii::t('app/crm', 'Created At'),
            'updated_at' => Yii::t('app/crm', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEntityFieldsValues()
    {
        return $this->hasMany(EntityFieldsValue::className(), ['field_id' => 'id']);
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        $parent = parent::behaviors();
        return ArrayHelper::merge($parent,[
            [
                'class' => CacheCustomTagBehavior::className(),
                'items' => [
                    'entity'
                ]
            ]
        ]);
    }

    /**
     * @return bool
     */
    public function beforeValidate()
    {
        if(empty($this->alias))
            $this->alias = CustomHelper::cyrillicToLatin($this->name);
        return parent::beforeValidate();
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if(empty($this->alias))
            $this->alias = CustomHelper::cyrillicToLatin($this->name);
        return parent::beforeSave($insert);
    }

    /**
     * @param $strEntity
     * @return mixed
     * @throws \Exception
     */
    public static function getEntityFieldsForModel($strEntity)
    {
        $obDep = new TagDependency([
            'tags' => self::getTagName('entity',$strEntity)
        ]);
        return self::getDb()->cache(function($db) use ($strEntity){
            self::find()->where(['entity' => $strEntity])->all($db);
        },86400,$obDep);
    }
}
