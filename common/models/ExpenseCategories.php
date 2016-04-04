<?php

namespace common\models;

use devgroup\TagDependencyHelper\ActiveRecordHelper;
use DevGroup\TagDependencyHelper\NamingHelper;
use Yii;
use yii\caching\DbDependency;
use yii\caching\TagDependency;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%expense_categories}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $description
 * @property integer $parent_id
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $without_cuser
 * @property integer $ignore_at_report
 * @property integer $private
 */
class ExpenseCategories extends AbstractActiveRecord
{

    private
        $changeParentId = false;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%expense_categories}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'],'unique','targetClass' => self::className(),
             'message' => Yii::t('app/services','This name has already been taken.')],
            [['parent_id', 'status', 'created_at', 'updated_at','without_cuser','ignore_at_report','private'], 'integer'],
            ['parent_id', 'default', 'value' => 0],
            [['name'], 'string', 'max' => 255],
            [['description'], 'string', 'max' => 32],
            ['ignore_at_report','default','value' => self::NO]
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
            'parent_id' => Yii::t('app/services', 'Parent ID'),
            'status' => Yii::t('app/services', 'Status'),
            'created_at' => Yii::t('app/services', 'Created At'),
            'updated_at' => Yii::t('app/services', 'Updated At'),
            'without_cuser' => Yii::t('app/services','Without cuser for expense'),
            'ignore_at_report' => Yii::t('app/services','Ignore at reports'),
            'private' => Yii::t('app/services','Only for admins')
        ];
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
     * получаем родительские категории
     * @param null $except
     * @return array
     */
    public static function getParentCat($except = NULL)
    {
        $dep =  new TagDependency(['tags' => NamingHelper::getCommonTag(self::className())]);
        $arCat = self::getDb()->cache(function($db){
            return self::find()->where(['parent_id' => 0])->all();
        },3600*24,$dep);

        $tmp = ArrayHelper::map($arCat ,'id','name');
        if(!is_null($except) && array_key_exists($except,$tmp))
        {
            unset($tmp[$except]);
        }

        return $tmp;
    }

    /**
     * @return mixed
     */
    public static function getAllExpenseCategories()
    {
        $dep =  new TagDependency(['tags' => NamingHelper::getCommonTag(self::className())]);
        $isAdmin = Yii::$app->user->can('superRights');
        $arCat = self::getDb()->cache(function($db) use ($isAdmin){
            $query = ExpenseCategories::find();
            if(!$isAdmin)
                $query->where('private = 0 OR private is NULL');
            return $query->all($db);
        },3600*24,$dep);

        $arResult = [];
        $arParent = [];
        foreach($arCat as $key=>$t)
            if($t->parent_id == 0 || empty($t->parent_id))
            {
                $arParent[] = $t->id;
                $arResult[]= $t;
                unset($arCat[$key]);
            }

        foreach($arCat as $key=>$t)
            if(in_array($t->parent_id,$arParent))
            {
                $arResult[]= $t;
                unset($arCat[$key]);
            }


        return $arResult;
    }

    /**
     * @return array
     */
    public static function getExpenseCatMap()
    {
        $tmp = self::getAllExpenseCategories();
        return ArrayHelper::map($tmp,'id','name');
    }

    /**
     * Получаем категории родительские без дочерних + родительские
     * @return array
     */
    public static function getExpenseCatMapWithoutParent()
    {
        $tmp = self::getAllExpenseCategories();
        $parent = [];
        foreach($tmp as $t)
            if(!empty($t->parent_id))
                $parent [] = $t->parent_id;

        $arResult = [];
        foreach($tmp as $cat)
        {
            if(!in_array($cat->id,$parent))
                $arResult[$cat->id] = $cat->name;
        }
        return $arResult;

    }


    /**
     * Опишем связь родителя
     * @return \yii\db\ActiveQuery
     */
    public function getParent()
    {
        return $this->hasOne(self::className(), ['id' => 'parent_id']);
    }

    /**
     *
     */
    public function afterDelete()
    {
        self::deleteAll(['parent_id' => $this->id]);
        return parent::afterDelete();
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if($this->isAttributeChanged('parent_id')) //елси изменился атрибут parent
        {
            $oldParentID = $this->getOldAttribute('parent_id');
            if((empty($oldParentID) || $oldParentID == 0) && $this->parent_id > 0)
            {
                $this->changeParentId = TRUE;
            }
        }

        return parent::beforeSave($insert);
    }

    /**
     * Необходимо проверить не менялся ли атрибут parent_id.
     * Так как по условию вложенность у нас иммеет только один уровень, то
     * если у родительскую категорию изменили на дочернюю, то все дочернии категории этой родительской
     * категории необходиом переназначить новой радительской.
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        if($this->changeParentId)
            self::updateAll(['parent_id' => $this->parent_id],'parent_id = :parent',['parent' => $this->id]);
        return parent::afterSave($insert, $changedAttributes);
    }


}
