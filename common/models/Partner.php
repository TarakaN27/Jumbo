<?php

namespace common\models;

use DevGroup\TagDependencyHelper\NamingHelper;
use Yii;
use yii\caching\TagDependency;

/**
 * This is the model class for table "{{%partner}}".
 *
 * @property integer $id
 * @property string $fname
 * @property string $lname
 * @property string $mname
 * @property string $description
 * @property string $email
 * @property string $phone
 * @property string $post_address
 * @property string $ch_account
 * @property string $psk
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 */
class Partner extends AbstractActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%partner}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['description', 'post_address', 'ch_account'], 'string'],
            //[['psk'], 'required'],
            ['psk','unique'],
            ['email','email'],
            [['status', 'created_at', 'updated_at'], 'integer'],
            [['fname', 'lname', 'mname', 'email', 'phone', 'psk'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/users', 'ID'),
            'fname' => Yii::t('app/users', 'Fname'),
            'lname' => Yii::t('app/users', 'Lname'),
            'mname' => Yii::t('app/users', 'Mname'),
            'description' => Yii::t('app/users', 'Description'),
            'email' => Yii::t('app/users', 'Email'),
            'phone' => Yii::t('app/users', 'Phone'),
            'post_address' => Yii::t('app/users', 'Post Address'),
            'ch_account' => Yii::t('app/users', 'Ch Account'),
            'psk' => Yii::t('app/users', 'Psk'),
            'status' => Yii::t('app/users', 'Status'),
            'created_at' => Yii::t('app/users', 'Created At'),
            'updated_at' => Yii::t('app/users', 'Updated At'),
        ];
    }

    /**
     * Get full name like Moroz Sergey Nikolaevich
     * @return string
     */
    public function getFio()
    {
        return ucwords($this->lname.' '.$this->fname.' '.$this->mname);
    }

    /**
     * Get short name like Moroz S. N.
     * @return string
     */
    public function getFioShort()
    {
        return ucwords($this->lname.' '.mb_substr($this->fname,0,1,'UTF-8').'. '.mb_substr($this->mname,0,1,'UTF-8').'.');
    }

    /**
     * @return bool
     */
    public function beforeValidate()
    {
        return parent::beforeValidate();
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        return parent::beforeSave($insert);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public static function getPartnerMap()
    {
        $obDep  = new TagDependency([
            'tags' => NamingHelper::getCommonTag(self::className())
        ]);

        $arTmp = self::getDb()->cache(function($db){
            return self::find()->select(['id', 'fname', 'lname', 'mname'])->all($db);
        },86400,$obDep);

        $arRes = [];
        foreach($arTmp as $tmp)
            $arRes[$tmp->id] = $tmp->getFio();

        return $arRes;
    }

    /**
     * @param $psk
     * @return mixed
     * @throws \Exception
     */
    public static function getPartnerByPsk($psk)
    {
        $obDep = new TagDependency([
            'tags' => self::getTagName('psk',$psk)
        ]);

        return self::getDb()->cache(function($db) use ($psk){
            return self::find()->where(['psk' => $psk])->one();
        },86400,$obDep);
    }

    /**
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        //инвалидируем кеш для определенного партнера
        if(!$insert)
            TagDependency::invalidate(Yii::$app->cache,[self::getTagName('psk',$this->psk)]);
        return parent::afterSave($insert, $changedAttributes);
    }
}
