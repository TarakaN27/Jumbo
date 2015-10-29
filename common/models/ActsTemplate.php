<?php

namespace common\models;

use common\components\behavior\UploadBehavior;
use DevGroup\TagDependencyHelper\NamingHelper;
use Yii;
use yii\caching\TagDependency;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%acts_template}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $path
 * @property integer $is_default
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property Acts[] $acts
 */
class ActsTemplate extends AbstractActiveRecord
{
    CONST
        FILE_PATH = '@common/upload/docx_template';

    protected
        $updateDefault = FALSE;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%acts_template}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['is_default', 'created_at', 'updated_at'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['path'],'required','on' => ['insert']],
            [['path'],'file', 'on' => ['insert', 'update']]
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
            'path' => Yii::t('app/documents', 'Path'),
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
                    'class' => UploadBehavior::className(),
                    'attribute' => 'path',
                    'scenarios' => ['insert', 'update'],
                    'path' => self::FILE_PATH.'/',
                    'url' => '',
                ],
            ]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getActs()
    {
        return $this->hasMany(Acts::className(), ['template_id' => 'id']);
    }

    /**
     * @return string
     */
    public function getFilePath()
    {
        return Yii::getAlias(self::FILE_PATH).'/'.$this->path;
    }


    /**
     * @return array
     */
    public static function getActsTplMap()
    {
        $dep = new TagDependency([
            'tags' => [
                NamingHelper::getCommonTag(self::className()),
            ]
        ]);

        $models = self::getDb()->cache(function ($db) {
            return self::find()->select(['id','name'])->orderBy(' is_default DESC ')->all($db);
        },86400,$dep);

        return ArrayHelper::map($models,'id','name');
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        //если устанавливаем текущую записб как дефолтную, то нужно убрать
        if(($insert && $this->is_default) || ($this->isAttributeChanged('is_default') && $this->is_default))
            $this->updateDefault = TRUE;

        return parent::beforeSave($insert);
    }

    /**
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        if($this->updateDefault)    //установим поле is_default для других записей в NO
            self::updateAll(
                ['is_default' => self::NO],
                'id != :id AND is_default = :def ',
                [':id' => $this->id,':def' => self::YES]
            );

        return parent::afterSave($insert, $changedAttributes);
    }
}
