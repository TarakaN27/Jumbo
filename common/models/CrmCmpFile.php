<?php

namespace common\models;

use common\components\behavior\UploadBehavior;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%crm_cmp_file}}".
 *
 * @property integer $id
 * @property integer $cmp_id
 * @property string $name
 * @property string $ext
 * @property string $src
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property CUser $cmp
 */
class CrmCmpFile extends AbstractActiveRecord
{
    CONST
        FILE_PATH = '@common/upload/crm_cmp_docs';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%crm_cmp_file}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name','cmp_id'],'required'],
            ['src','required', 'on' => ['insert']],
            [['cmp_id', 'created_at', 'updated_at'], 'integer'],
            [['name', 'ext'], 'string', 'max' => 255],
            [['src'], 'file','on' => ['insert']]
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
            'name' => Yii::t('app/crm', 'Name'),
            'ext' => Yii::t('app/crm', 'Ext'),
            'src' => Yii::t('app/crm', 'Src'),
            'created_at' => Yii::t('app/crm', 'Created At'),
            'updated_at' => Yii::t('app/crm', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCmp()
    {
        return $this->hasOne(CUser::className(), ['id' => 'cmp_id']);
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
                    'attribute' => 'src',
                    'scenarios' => ['insert'],
                    'path' => self::FILE_PATH.'/',
                    'url' => '',
                ],
            ]);
    }

    public function beforeSave($insert)
    {
        if(parent::beforeSave($insert))
        {
            if($insert || $this->isAttributeChanged('src'))
            {
                $this->ext = end(explode('.',$this->src));
            }
            return TRUE;
        }

        return FALSE;
    }

    /**
     * @return string
     */
    public function getSplitName()
    {
        return $this->name.'.'.$this->ext;
    }

    public function getHtmlClassExt()
    {
        $str = 'fa-file';
        switch($this->ext)
        {
            case 'jpg':
            //case 'jpeg':

                break;
            default:
                break;
        }
    }
}
