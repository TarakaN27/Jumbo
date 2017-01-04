<?php

namespace common\models;

use backend\models\BUser;
use common\components\acts\ActsDocuments;
use common\components\acts\ActsDocumentsV2;
use common\components\acts\PartnerProfitActBehavior;
use common\components\behavior\acts\ActsActionBehavior;
use common\components\behavior\UploadBehavior;
use common\components\customComponents\validation\ValidNumber;
use common\components\entityFields\EntityFieldsTrait;
use common\components\helpers\CustomHelper;
use common\components\loggingUserBehavior\LogModelBehavior;
use Yii;
use yii\caching\TagDependency;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

/**
 * This is the model class for table "{{%acts}}".
 *
 * @property integer $id
 * @property integer $cuser_id
 * @property integer $buser_id
 * @property string $amount
 * @property string $act_date
 * @property integer $sent
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $act_num
 * @property integer $lp_id
 * @property string $file_name
 * @property string $ask
 * @property string $contract_date
 * @property string $contract_num
 * @property integer $currency_id
 *
 * @property ActsTemplate $template
 * @property BUser $buser
 * @property CUser $cuser
 * @property Services $service
 */
class Acts extends AbstractActiveRecord
{
    use EntityFieldsTrait; //подключаем доп настройки
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
            [['act_num','cuser_id', 'buser_id','lp_id', 'amount'], 'required'],
            ['amount',ValidNumber::className()],
            [['amount'],'number','numberPattern' => '/^\s*[-+]?[0-9\s]*[\.,\s]?[0-9]+([eE][-+]?[0-9]+)?\s*$/'],
            [[
                'act_num','cuser_id','lp_id' ,
                'buser_id',
                'sent', 'created_at',
                'updated_at','genFile','currency_id'], 'integer'],
            [['act_date','entityFields'], 'safe'],
            [['ask'],'unique'],
            //[['act_date','contract_date'],'date', 'format' => 'yyyy-m-dd'],
            [['ask','contract_num'], 'string', 'max' => 255],
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
            'amount' => Yii::t('app/documents', 'Amount'),
            'act_date' => Yii::t('app/documents', 'Act Date'),
            'sent' => Yii::t('app/documents', 'Sent'),
            'created_at' => Yii::t('app/documents', 'Created At'),
            'updated_at' => Yii::t('app/documents', 'Updated At'),
            'act_num' => Yii::t('app/documents', 'Act number'),
            'lp_id' =>  Yii::t('app/documents', 'Legal person'),
            'file_name' => Yii::t('app/documents', 'File name'),
            'ask' => Yii::t('app/documents', 'Act secret key'),
            'genFile' => Yii::t('app/documents','Generate document'),
            'contract_num' => Yii::t('app/documents', 'Contract number'),
            'contract_date' => Yii::t('app/documents', 'Contract date'),
            'currency_id' => Yii::t('app/documents','Currency id')
        ];
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
    public function getServices()
    {
        return $this->hasMany(ActServices::className(),['act_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLegalPerson()
    {
        return $this->hasOne(LegalPerson::className(),['id' => 'lp_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCurrency()
    {
        return $this->hasOne(ExchangeRates::className(),['id' => 'currency_id']);
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
     * @throws NotFoundHttpException
     */
    public function beforeSave($insert)
    {
        if($insert)
            $this->ask = Yii::$app->security->generateRandomString(); //generate unique key for act

        if(!CustomHelper::isDirExist(self::FILE_PATH))
            throw new NotFoundHttpException('Folder for acts is not exist. Path: '.self::FILE_PATH);

        return parent::beforeSave($insert);
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
            ],
            [
                'class' => LogModelBehavior::className(),       //логирование актов
                'ignored' => ['created_at','updated_at']
            ],
            ActsActionBehavior::className()                     //действия по событиям
        ]);
    }

    /**
     * @param $ask
     * @param bool|FALSE $onlySent
     * @return mixed
     */
    public static function getOneByAsk($ask,$onlySent = FALSE)
    {
        if($onlySent)
            return self::find()->where(['ask' => $ask,'sent' => self::YES])->one();
        else
            return self::find()->where(['ask' => $ask])->one();
    }

    /**
     * @return $this
     */
    public function getDocument()
    {
        return Yii::$app->response->sendFile(Yii::getAlias(self::FILE_PATH).'/'.$this->file_name);
    }

    /**
     * 
     */
    public function afterDelete()
    {
        @unlink(Yii::getAlias(self::FILE_PATH).'/'.$this->file_name); //удалим акт
        parent::afterDelete();
    }

    /**
     * @param $legalPersonId
     * @return int
     */
    public static function getNextActNumber($legalPersonId,$year)
    {

        $lastNumber = self::find()->where(['lp_id' => $legalPersonId])->andWhere(['>=', 'act_date', $year.'-01-01'])->andWhere(['<=', 'act_date', ($year+1).'-01-01'])->select(['act_num'])->orderBy(['created_at'=>SORT_DESC])->one();
        if(!$lastNumber)
            return 1;
        return $lastNumber->act_num+1;
    }

    /**
     * @return string
     */
    public function getDocumentPath()
    {
        return Yii::getAlias(self::FILE_PATH).'/'.$this->file_name;
    }

    public static function generateXmlFor1C($start, $end){
        $dom = new \DOMDocument("1.0", "utf-8");
        $root = $dom->createElement("Акты"); // Создаём корневой элемент
        $acts = Acts::find()->andWhere(['>=','act_date', $start])->andWhere(['<=','act_date', $end])->andWhere(['lp_id'=>3])->all();
        foreach($acts as $act){
            $obActDoc = new ActsDocumentsV2($act->id,$act->lp_id,$act->cuser_id,$act->act_date,$act->act_num,$act->currency_id);
            $actNode = $obActDoc->generateNodeActXml($dom,$act);
            $root->appendChild($actNode);
        }
        $dom->appendChild($root);
        return $dom;
    }
}
