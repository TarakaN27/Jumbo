<?php

namespace common\models;

use DevGroup\TagDependencyHelper\NamingHelper;
use Yii;
use yii\caching\TagDependency;

/**
 * This is the model class for table "{{%cuser_prefer_pay_cond}}".
 *
 * @property integer $id
 * @property integer $cuser_id
 * @property integer $service_id
 * @property integer $cond_id
 *
 * @property Services $service
 * @property PaymentCondition $cond
 * @property CUser $cuser
 */
class CuserPreferPayCond extends AbstractActiveRecordWTB
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%cuser_prefer_pay_cond}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['cuser_id', 'service_id', 'cond_id'], 'required'],
            [['cuser_id', 'service_id', 'cond_id'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/book', 'ID'),
            'cuser_id' => Yii::t('app/book', 'Cuser ID'),
            'service_id' => Yii::t('app/book', 'Service ID'),
            'cond_id' => Yii::t('app/book', 'Cond ID'),
        ];
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
    public function getCond()
    {
        return $this->hasOne(PaymentCondition::className(), ['id' => 'cond_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCuser()
    {
        return $this->hasOne(CUser::className(), ['id' => 'cuser_id']);
    }

    /**
     * @param array $usersID
     * @return mixed
     * @throws \Exception
     */
    public static function getPreferCondForUsers(array $usersID)
    {
        $obDep = new TagDependency([
            'tags' => NamingHelper::getCommonTag(self::className())
        ]);

        return self::getDb()->cache(function($db) use ($usersID){
            return self::find()->where(['cuser_id' => $usersID])->all();
        },86400,$obDep);
    }
}
