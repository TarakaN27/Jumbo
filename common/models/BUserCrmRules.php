<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%b_user_crm_rules}}".
 *
 * @property integer $id
 * @property integer $role_id
 * @property string $entity
 * @property integer $crt
 * @property integer $rd
 * @property integer $upd
 * @property integer $del
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property BUserCrmRoles $role
 */
class BUserCrmRules extends AbstractActiveRecord
{
    CONST
        RULE_ALL = 1,
        RULE_OPENED = 2,
        RULE_THEMSELF = 3,
        RULE_CLOSED = 4;

    CONST
        READ_ACTION = 'rd',
        CREATE_ACTION = 'crt',
        UPDATE_ACTION = 'upd',
        DELETE_ACTION = 'del';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%b_user_crm_rules}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['role_id', 'crt', 'rd', 'upd', 'del', 'created_at', 'updated_at'], 'integer'],
            [['crt', 'rd', 'upd', 'del'], 'default', 'value' => self::RULE_CLOSED],
            [['crt', 'rd', 'upd', 'del'], 'in', 'range' => array_keys(self::getRuleArr())],
            [['entity'], 'required'],
            [['entity'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/crm', 'ID'),
            'role_id' => Yii::t('app/crm', 'Role ID'),
            'entity' => Yii::t('app/crm', 'Entity'),
            'crt' => Yii::t('app/crm', 'Crt'),
            'rd' => Yii::t('app/crm', 'Rd'),
            'upd' => Yii::t('app/crm', 'Upd'),
            'del' => Yii::t('app/crm', 'Del'),
            'created_at' => Yii::t('app/crm', 'Created At'),
            'updated_at' => Yii::t('app/crm', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRole()
    {
        return $this->hasOne(BUserCrmRoles::className(), ['id' => 'role_id']);
    }


    /**
     * Сущности для которых задаются правила
     * @return array
     */
    public static function getEntityArr()
    {
        return [
            CUser::getModelName() => Yii::t('app/crm','Company')
        ];
    }

    /**
     * @return array
     */
    public static function getRuleArr()
    {
        return [
            self::RULE_CLOSED => Yii::t('app/crm','Closed'),
            self::RULE_ALL => Yii::t('app/crm','All'),
            self::RULE_OPENED => Yii::t('app/crm','Opened'),
            self::RULE_THEMSELF => Yii::t('app/crm','Themself')
        ];
    }

    /**
     * @return string
     */
    public function getReadStr()
    {
        $tmp = self::getRuleArr();
        return isset($tmp[$this->rd]) ? $tmp[$this->rd] : 'N/A';
    }

    /**
     * @return string
     */
    public function getUpdateStr()
    {
        $tmp = self::getRuleArr();
        return isset($tmp[$this->upd]) ? $tmp[$this->upd] : 'N/A';
    }

    /**
     * @return string
     */
    public function getCreateStr()
    {
        $tmp = self::getRuleArr();
        return isset($tmp[$this->crt]) ? $tmp[$this->crt] : 'N/A';
    }

    /**
     * @return string
     */
    public function getDeleteStr()
    {
        $tmp = self::getRuleArr();
        return isset($tmp[$this->del]) ? $tmp[$this->del] : 'N/A';
    }

    /**
     * @return array
     */
    public static function getAllowedAction()
    {
        return [self::READ_ACTION,self::CREATE_ACTION,self::UPDATE_ACTION,self::DELETE_ACTION];
    }

}
