<?php

namespace common\models;

use Yii;
use yii\web\ServerErrorHttpException;

/**
 * This is the model class for table "{{%partner_cuser_serv}}".
 *
 * @property integer $id
 * @property integer $partner_id
 * @property integer $cuser_id
 * @property integer $service_id
 * @property string $connect
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $archive
 *
 * @property CUser $cuser
 * @property CUser $partner
 * @property Services $service
 */
class PartnerCuserServ extends AbstractActiveRecord
{

    public
        $archiveDate = NULL;

    CONST
        SCENARIO_ARCHIVE = 'archive',
        EVENT_BEFORE_ARCHIVE = 'before_archive',
        EVENT_AFTER_ARCHIVE = 'after_archive';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%partner_cuser_serv}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['partner_id', 'cuser_id', 'service_id'], 'required','except' => self::SCENARIO_ARCHIVE],
            [['partner_id', 'cuser_id', 'service_id', 'created_at', 'updated_at', 'archive'], 'integer'],
            [['connect','archiveDate'], 'safe'],
            [['service_id','cuser_id'],'uniqueValid','except' => self::SCENARIO_ARCHIVE],
            [['cuser_id'], 'exist', 'skipOnError' => true, 'targetClass' => CUser::className(), 'targetAttribute' => ['cuser_id' => 'id'],'except' => self::SCENARIO_ARCHIVE],
            [['partner_id'], 'exist', 'skipOnError' => true, 'targetClass' => CUser::className(), 'targetAttribute' => ['partner_id' => 'id'],'except' => self::SCENARIO_ARCHIVE],
            [['service_id'], 'exist', 'skipOnError' => true, 'targetClass' => Services::className(), 'targetAttribute' => ['service_id' => 'id'],'except' => self::SCENARIO_ARCHIVE],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/users', 'ID'),
            'partner_id' => Yii::t('app/users', 'Partner ID'),
            'cuser_id' => Yii::t('app/users', 'Cuser ID'),
            'service_id' => Yii::t('app/users', 'Service ID'),
            'connect' => Yii::t('app/users', 'Connect'),
            'created_at' => Yii::t('app/users', 'Created At'),
            'updated_at' => Yii::t('app/users', 'Updated At'),
            'archive' => Yii::t('app/users', 'Archive'),
        ];
    }

    /**
     * Проверяем, чтобы для каждого партнера была одна уникальная связка
     * @param $attribute
     * @param $param
     */
    public function uniqueValid($attribute,$param)
    {
        if(self::find()->where([
            'partner_id' => $this->partner_id,
            'cuser_id' => $this->cuser_id,
            'service_id' => $this->service_id
        ])->exists())
            $this->addError($attribute,Yii::t('app/users','Link already exists'));
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
    public function getPartner()
    {
        return $this->hasOne(CUser::className(), ['id' => 'partner_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getService()
    {
        return $this->hasOne(Services::className(), ['id' => 'service_id']);
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if(!empty($this->connect))
            $this->connect = date('Y-m-d',strtotime($this->connect));

        return parent::beforeSave($insert);
    }

    /**
     * @return int
     * @throws ServerErrorHttpException
     */
    public function archive()
    {
        $this->setScenario(self::SCENARIO_ARCHIVE);
        $this->callTriggerBeforeArchive();
        if ($this->archive)
        {
            $this->archive = self::NO;
        }else{
            $this->archive = self::YES;
        }
        if(!$this->save())
            throw new ServerErrorHttpException();

        $this->callTriggerAfterArchive();
        return $this->archive;
    }

    /**
     *
     */
    public function callTriggerBeforeArchive()
    {
        $this->trigger(self::EVENT_BEFORE_ARCHIVE);
    }

    /**
     *
     */
    public function callTriggerAfterArchive()
    {
        $this->trigger(self::EVENT_AFTER_ARCHIVE);
    }
}
