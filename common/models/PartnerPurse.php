<?php

namespace common\models;

use common\components\loggingUserBehavior\LogModelBehavior;
use Yii;
use yii\caching\TagDependency;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%partner_purse}}".
 *
 * @property integer $id
 * @property integer $partner_id
 * @property string $payments
 * @property string $acts
 * @property string $amount
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property Partner $partner
 */
class PartnerPurse extends AbstractActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%partner_purse}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['partner_id'], 'required'],
            ['partner_id','unique'],
            [['partner_id', 'created_at', 'updated_at'], 'integer'],
            [['payments', 'acts', 'amount'], 'number']
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
            'payments' => Yii::t('app/users', 'Purse payments'),
            'acts' => Yii::t('app/users', 'Purse acts'),
            'amount' => Yii::t('app/users', 'Purse amount'),
            'created_at' => Yii::t('app/users', 'Created At'),
            'updated_at' => Yii::t('app/users', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPartner()
    {
        return $this->hasOne(Partner::className(), ['id' => 'partner_id']);
    }

    /**
     * @param $partnerID
     * @return mixed
     * @throws \Exception
     */
    public static function getPurse($partnerID)
    {
        $obDep = new TagDependency([
            'tags' => [
                self::getTagName('partner_id',$partnerID)
            ]
        ]);

        return self::getDb()->cache(function($db) use ($partnerID){
            return self::find()->where(['partner_id' => $partnerID])->one($db);
        },86400,$obDep);
    }

    /**
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        //инвалидируем кеш для определенного партнера
        //if(!$insert)
            TagDependency::invalidate(Yii::$app->cache,[self::getTagName('partner_id',$this->partner_id)]);
        return parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @param $pID
     * @return mixed
     */
    public static function getPurseNotCached($pID)
    {
        return self::find()->where(['partner_id' => $pID])->one();
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        $pb = parent::behaviors();
        return ArrayHelper::merge($pb,[
            [
                'class' => LogModelBehavior::className(),       //логирование изменения
                'ignored' => ['created_at','updated_at']
            ],
        ]);
    }
}
