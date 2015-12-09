<?php

namespace common\models;

use common\components\behavior\CacheCustomTagBehavior\CacheCustomTagBehavior;
use Yii;
use yii\caching\TagDependency;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%entity_fields_value}}".
 *
 * @property integer $id
 * @property string $entity
 * @property integer $item_id
 * @property integer $field_id
 * @property string $value
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property EntityFields $field
 */
class EntityFieldsValue extends AbstractActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%entity_fields_value}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['entity', 'item_id', 'field_id', 'value'], 'required'],
            [['item_id', 'field_id', 'created_at', 'updated_at'], 'integer'],
            [['value'], 'string'],
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
            'entity' => Yii::t('app/crm', 'Entity'),
            'item_id' => Yii::t('app/crm', 'Item ID'),
            'field_id' => Yii::t('app/crm', 'Field ID'),
            'value' => Yii::t('app/crm', 'Value'),
            'created_at' => Yii::t('app/crm', 'Created At'),
            'updated_at' => Yii::t('app/crm', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getField()
    {
        return $this->hasOne(EntityFields::className(), ['id' => 'field_id']);
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
     * @param $arIds
     * @param $entity
     * @param $itemID
     * @return mixed
     * @throws \Exception
     */
    public static function getFieldsValue($arIds,$entity,$itemID)
    {
        $obDep = new TagDependency([
            'tags' => self::getTagName('entity',$entity)
        ]);
        return self::getDb()->cache(function() use($arIds,$entity,$itemID){
            return self::find()->where(['field_id' => $arIds,'entity'=> $entity,'item_id' => $itemID])->all();
        },86400,$obDep);

    }
}
