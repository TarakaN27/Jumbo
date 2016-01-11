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
 * @property integer $contact_id
 * @property integer $task_id
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
            [['name'],'required'],
            ['src','required', 'on' => ['insert']],
            [['cmp_id', 'created_at', 'updated_at','contact_id','task_id'], 'integer'],
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
            'contact_id' => Yii::t('app/crm', 'CRM contact'),
            'task_id' => Yii::t('app/crm','Task')
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
     * @return \yii\db\ActiveQuery
     */
    public function getContact()
    {
        return $this->hasOne(CrmCmpContacts::className(),['id' => 'contact_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTask()
    {
        return $this->hasOne(CrmTask::className(),['id' => 'task_id']);
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

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if(parent::beforeSave($insert))
        {
            if($insert || $this->isAttributeChanged('src'))
            {
                $tmp = explode('.',$this->src);
                if(is_array($tmp))
                    $this->ext = end($tmp);
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

    /**
     * @return string
     */
    public function getHtmlClassExt()
    {
        $str = 'fa-file';
        switch($this->ext)
        {
            case 'jpg':
            case 'jpeg':
            case 'png':
                $str = 'fa-file-image-o';
                break;

            case 'docx':
            case 'doc':
                $str = 'fa-file-word-o';
                break;

            case 'zip':
            case 'tar':
            case 'rar':
                $str = 'fa-file-archive-o';
                break;

            case 'txt':
                $str = 'fa-file-text-o';
                break;

            case 'xls':
            case 'xlsx':
                $str = 'fa-file-excel-o';
                break;

            case 'pdf':
                $str = 'fa-file-pdf-o';
                break;

            case 'ppt':
            case 'pot':
            case 'pptx':
            case 'potx':

            default:
                break;
        }

        return 'fa '.$str;
    }

    /**
     * @return string
     */
    public function getFilePath()
    {
        return Yii::getAlias(self::FILE_PATH.'/'.$this->src);
    }
}
