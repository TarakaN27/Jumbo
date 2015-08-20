<?php

namespace common\models;

use devgroup\TagDependencyHelper\ActiveRecordHelper;
use Yii;
use yii\caching\TagDependency;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%bill_template}}".
 *
 * @property integer $id
 * @property string $name
 * @property integer $l_person_id
 * @property integer $service_id
 * @property string $object_text
 * @property string $description
 * @property integer $use_vat
 * @property string $vat_rate
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property Services $service
 * @property LegalPerson $lPerson
 */
class BillTemplate extends AbstractActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%bill_template}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'l_person_id', 'service_id', 'object_text'], 'required'],
            [['l_person_id', 'service_id', 'use_vat', 'created_at', 'updated_at'], 'integer'],
            [['object_text', 'description'], 'string'],
            [['vat_rate'], 'number','min' => 0],
            [['vat_rate'], 'number','max' => 100],
            [['name'], 'string', 'max' => 255],
            ['name','unique'],
            [['vat_rate'],'required',
             'when' => function($model) {
                     return $model->use_vat == self::YES;
                 },
             'whenClient' => "function (attribute, value) {
                    return $('#billtemplate-use_vat').val() == '".self::YES."';
                }"
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/documents', 'ID'),
            'name' => Yii::t('app/documents', 'Name'),
            'l_person_id' => Yii::t('app/documents', 'L Person ID'),
            'service_id' => Yii::t('app/documents', 'Service ID'),
            'object_text' => Yii::t('app/documents', 'Object Text'),
            'description' => Yii::t('app/documents', 'Description'),
            'use_vat' => Yii::t('app/documents', 'Use Vat'),
            'vat_rate' => Yii::t('app/documents', 'Vat Rate'),
            'created_at' => Yii::t('app/documents', 'Created At'),
            'updated_at' => Yii::t('app/documents', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getService()
    {
        return $this->hasOne(Services::className(), ['id' => 'service_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLPerson()
    {
        return $this->hasOne(LegalPerson::className(), ['id' => 'l_person_id']);
    }

    /**
     * @return mixed
     */
    public static function getBillTemplateArr()
    {
        $dep = new TagDependency([
            'tags' => [
                ActiveRecordHelper::getCommonTag(self::className()),
            ]
        ]);

        $models = self::getDb()->cache(function ($db) {
            return self::find()->all($db);
        },86400,$dep);

        return $models;
    }

    /**
     * @return array
     */
    public static function getBillTemplateMap()
    {
        $tmp = self::getBillTemplateArr();
        return ArrayHelper::map($tmp,'id','name');
    }
}
