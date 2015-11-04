<?php

namespace common\models;

use DevGroup\TagDependencyHelper\NamingHelper;
use Yii;

use yii\caching\TagDependency;

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
 *
 * @property CUser $cuser
 * @property Partner $partner
 */
class PartnerCuserServ extends AbstractActiveRecord
{
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
            [['partner_id', 'cuser_id','service_id'], 'required'],
            [['partner_id', 'cuser_id','service_id', 'created_at', 'updated_at'], 'integer'],
            ['connect','date', 'format' => 'yyyy-m-dd'],
            [['connect'], 'safe'],
            [['service_id','cuser_id'],'uniqueValid']
        ];
    }


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
        ];
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
        return $this->hasOne(Partner::className(), ['id' => 'partner_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getService()
    {
        return $this->hasOne(Services::className(),['id' => 'service_id']);
    }

    /**
     * @param $partnerID
     * @return mixed
     * @throws \Exception
     */
    public static function getLinkedServices($partnerID)
    {
        $obDep  = new TagDependency([
            'tags' => NamingHelper::getCommonTag(self::className())
        ]);
        return self::getDb()->cache(function($db) use ($partnerID){
            return self::find()->where(['partner_id' => $partnerID])->all($db);
        },86400,$obDep);
    }
}
