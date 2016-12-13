<?php

namespace common\models;

use devgroup\TagDependencyHelper\ActiveRecordHelper;
use DevGroup\TagDependencyHelper\NamingHelper;
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
 * @property string $offer_contract
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
            [['name', 'l_person_id', 'service_id', 'object_text','offer_contract'], 'required'],
            [['object_text', 'description','name','offer_contract','validity'],'trim'],
            [['l_person_id', 'service_id',  'created_at', 'updated_at'], 'integer'],
            [['object_text', 'description'], 'string'],
            [['name','offer_contract'], 'string', 'max' => 255],
            ['name','unique'],

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
            'created_at' => Yii::t('app/documents', 'Created At'),
            'updated_at' => Yii::t('app/documents', 'Updated At'),
            'offer_contract' => Yii::t('app/documents','offer_contract'),
            'validity' => Yii::t('app/documents','Validity'),
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
                NamingHelper::getCommonTag(self::className()),
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
