<?php

namespace common\models;

use DevGroup\TagDependencyHelper\NamingHelper;
use Yii;
use yii\caching\TagDependency;

/**
 * This is the model class for table "{{%cuser_external_account}}".
 *
 * @property integer $id
 * @property integer $cuser_id
 * @property integer $type
 * @property string $login
 * @property string $password
 * @property string $secret_key
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property CUser $cuser
 */
class CuserExternalAccount extends AbstractActiveRecord
{
    CONST
        TYPE_CSDA = 1;

    /**
     * @return array
     */
    public static function getTypeArr()
    {
        return [
            self::TYPE_CSDA => Yii::t('app/users','CSDA system')
        ];
    }

    /**
     * @return string
     */
    public function getTypeStr()
    {
        $typeArr = self::getTypeArr();
        return isset($typeArr[$this->type]) ? $typeArr[$this->type] : 'N/A';
    }


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%cuser_external_account}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['cuser_id', 'login', 'password', 'secret_key'], 'required'],
            ['login', 'unique', 'targetClass' => self::className(),
                'message' => Yii::t('app/users','This login has already been taken.')],
            [['cuser_id', 'created_at', 'updated_at','type'], 'integer'],
            [['login', 'password', 'secret_key'], 'string', 'max' => 255],
            ['type', 'in', 'range' => array_keys(self::getTypeArr())],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/users', 'ID'),
            'type' => Yii::t('app/users', 'Type'),
            'cuser_id' => Yii::t('app/users', 'Cuser ID'),
            'login' => Yii::t('app/users', 'Login'),
            'password' => Yii::t('app/users', 'Password'),
            'secret_key' => Yii::t('app/users', 'Secret Key'),
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
     * @param $userID
     * @param $type
     * @return mixed
     */
    public static function getExtAccForUser($userID,$type)
    {
        return self::find()->where(['cuser_id' => $userID,'type' => $type])->one();
    }

    /**
     * @param array $secretKeys
     * @return mixed
     * @throws \Exception
     */
    public static function findAllBySecretKeyCached(array $secretKeys)
    {
        $obDep = new TagDependency([
            'tags' => NamingHelper::getCommonTag(self::className())
        ]);

        return self::getDb()->cache(
            function ($db) use ($secretKeys){
                return self::find()->select(['secret_key','cuser_id'])->where(['secret_key' => $secretKeys])->all($db);
            }, 86400, $obDep);
    }

}
