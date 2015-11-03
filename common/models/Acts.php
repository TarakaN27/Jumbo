<?php

namespace common\models;

use backend\models\BUser;
use common\components\acts\ActsDocuments;
use common\components\behavior\UploadBehavior;
use common\components\helpers\CustomHelper;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

/**
 * This is the model class for table "{{%acts}}".
 *
 * @property integer $id
 * @property integer $cuser_id
 * @property integer $buser_id
 * @property integer $service_id
 * @property integer $template_id
 * @property string $amount
 * @property string $act_date
 * @property integer $sent
 * @property integer $change
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $act_num
 * @property integer $lp_id
 * @property string $file_name
 * @property string $ask
 * @property string $contract_date
 * @property string $contract_num
 *
 * @property ActsTemplate $template
 * @property BUser $buser
 * @property CUser $cuser
 * @property Services $service
 */
class Acts extends AbstractActiveRecord
{

    public
        $genFile = 0, //сгенерировать файл
        $contNotif = 0, // уведомить контрагента
        $updateFile = 0; // обновить файл

    CONST
        FILE_PATH = '@common/upload/docx_acts';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%acts}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['act_num','cuser_id', 'buser_id','lp_id', 'service_id', 'template_id', 'amount'], 'required'],
            [[
                'act_num','cuser_id','lp_id' ,
                'buser_id', 'service_id', 'template_id',
                'sent', 'change', 'created_at',
                'updated_at','genFile'], 'integer'],
            [['act_date'], 'safe'],
            [['ask'],'unique'],
            [['act_date','contract_date'],'date', 'format' => 'yyyy-m-dd'],
            [['amount','ask','contract_num'], 'string', 'max' => 255],
            ['file_name','file','on' => ['insert', 'update'],'when' => function($model) {
                return !$model->genFile;
            }],
            ['file_name','required','on' => ['insert'],'when' => function($model) {
                    return !$model->genFile;
                }],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/documents', 'ID'),
            'cuser_id' => Yii::t('app/documents', 'Cuser ID'),
            'buser_id' => Yii::t('app/documents', 'Buser ID'),
            'service_id' => Yii::t('app/documents', 'Service ID'),
            'template_id' => Yii::t('app/documents', 'Template ID'),
            'amount' => Yii::t('app/documents', 'Amount'),
            'act_date' => Yii::t('app/documents', 'Act Date'),
            'sent' => Yii::t('app/documents', 'Sent'),
            'change' => Yii::t('app/documents', 'Change'),
            'created_at' => Yii::t('app/documents', 'Created At'),
            'updated_at' => Yii::t('app/documents', 'Updated At'),
            'act_num' => Yii::t('app/documents', 'Act number'),
            'lp_id' =>  Yii::t('app/documents', 'Legal person'),
            'file_name' => Yii::t('app/documents', 'File name'),
            'ask' => Yii::t('app/documents', 'Act secret key'),
            'genFile' => Yii::t('app/documents','Generate document'),
            'contract_num' => Yii::t('app/documents', 'Contract number'),
            'contract_date' => Yii::t('app/documents', 'Contract date')
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTemplate()
    {
        return $this->hasOne(ActsTemplate::className(), ['id' => 'template_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBuser()
    {
        return $this->hasOne(BUser::className(), ['id' => 'buser_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCuser()
    {
        return $this->hasOne(CUser::className(), ['id' => 'cuser_id']);
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
    public function getLegalPerson()
    {
        return $this->hasOne(LegalPerson::className(),['id' => 'lp_id']);
    }

    /**
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        return parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @param bool $insert
     * @return bool
     * @throws \yii\web\NotFoundHttpException
     * @throws \yii\web\ServerErrorHttpException
     */
    public function beforeSave($insert)
    {
        $this->ask = Yii::$app->security->generateRandomString(); //уникальный ключ для файла
        if(!CustomHelper::isDirExist(self::FILE_PATH))
            throw new NotFoundHttpException('Folder for acts is not exist. Path: '.self::FILE_PATH);

        if(parent::beforeSave($insert))
        {
            if($this->genFile)
            {
                $obActs = new ActsDocuments(
                    $this->act_num,
                    $this->act_date,
                    $this->template_id,
                    $this->lp_id,
                    $this->cuser_id,
                    $this->service_id,
                    $this->amount,
                    $this->contract_num,
                    $this->contract_date
                );
                $fileName = $obActs->generatePDF();
                if(empty($fileName))
                    throw new ServerErrorHttpException('Can not create document.');
                $this->file_name = $fileName;
            }
            return TRUE;
        }

        return FALSE;
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        $pb = parent::behaviors();
        return ArrayHelper::merge($pb,[
            [
                'class' => UploadBehavior::className(),
                'attribute' => 'file_name',
                'scenarios' => ['insert', 'update'],
                'path' => self::FILE_PATH.'/',
                'url' => ''
            ]
        ]);
    }

    /**
     * @return $this
     */
    public function getDocument()
    {
        return Yii::$app->response->sendFile(Yii::getAlias(self::FILE_PATH).'/'.$this->file_name);
    }
}
