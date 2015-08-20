<?php

namespace common\models;

use common\components\behavior\UploadBehavior;
use common\components\helpers\CustomHelper;
use devgroup\TagDependencyHelper\ActiveRecordHelper;
use Yii;
use yii\caching\TagDependency;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%bill_docx_template}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $src
 * @property integer $is_default
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property Bills[] $bills
 */
class BillDocxTemplate extends AbstractActiveRecord
{
    CONST
        FILE_PATH = '@common/upload/docx_template';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%bill_docx_template}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['is_default', 'created_at', 'updated_at'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['src'],'required','on' => ['insert', 'update']],
            [['src'],'file', 'on' => ['insert', 'update']]
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
            'src' => Yii::t('app/documents', 'Src'),
            'is_default' => Yii::t('app/documents', 'Is Default'),
            'created_at' => Yii::t('app/documents', 'Created At'),
            'updated_at' => Yii::t('app/documents', 'Updated At'),
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
                [
                    'class' => ActiveRecordHelper::className(),
                ],
                [
                    'class' => UploadBehavior::className(),
                    'attribute' => 'src',
                    'scenarios' => ['insert', 'update'],
                    'path' => self::FILE_PATH.'/{id}',
                    'url' => '',
                ],
            ]);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBills()
    {
        return $this->hasMany(Bills::className(), ['docx_tmpl_id' => 'id']);
    }

    public function downloadFile()
    {
        //CustomHelper::getDocument()
    }

    /**
     * @return mixed
     */
    public static function getBillDocxArray()
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
    public static function getBillDocxMap()
    {
        $dep = new TagDependency([
            'tags' => [
                ActiveRecordHelper::getCommonTag(self::className()),
            ]
        ]);

        $models = self::getDb()->cache(function ($db) {
            return self::find()->select(['id','name'])->orderBy(' is_default DESC ')->all($db);
        },86400,$dep);

        return ArrayHelper::map($models,'id','name');
    }
}
