<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%cuser_to_group}}".
 *
 * @property integer $cuser_id
 * @property integer $group_id
 *
 * @property CUserGroups $group
 * @property CUser $cuser
 */
class CuserToGroup extends AbstractActiveRecordWTB
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%cuser_to_group}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['cuser_id', 'group_id'], 'required'],
            [['cuser_id', 'group_id'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'cuser_id' => Yii::t('app/users', 'Cuser ID'),
            'group_id' => Yii::t('app/users', 'Group ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGroup()
    {
        return $this->hasOne(CUserGroups::className(), ['id' => 'group_id']);
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
     * @return array
     */
    public static function getUserByGroup($userID)
    {
        $iGroup = self::find()->where(['cuser_id' => $userID])->one();      //get group id for user
        if(!empty($iGroup))     //if user have group
        {
            $query = CUser::find()  //find all user for user group
            ->select([
                CUser::tableName().'.id',
                CUser::tableName().'.requisites_id',
                CUser::tableName().'.type',
                CUser::tableName().'.archive',
                CuserToGroup::tableName().'.group_id',
                CuserToGroup::tableName().'.cuser_id',
                CUserRequisites::tableName().'.id',
                CUserRequisites::tableName().'.type_id',
                CUserRequisites::tableName().'.j_lname',
                CUserRequisites::tableName().'.j_fname',
                CUserRequisites::tableName().'.j_mname'
            ])
                ->joinWith('cmpGroup')
                ->joinWith('requisites')
                ->where([
                    CuserToGroup::tableName().'.group_id' => $iGroup->group_id
                ])
                ->notArchive()
                ->all();
            $arResult = ArrayHelper::map($query,'id','infoWithSite');
        }else{
            $arResult = [ $userID => CUser::getCuserInfoById($userID)];
        }

        return $arResult;
    }

    /**
     * Return all users at group with having $iUserId
     * @param $iUserId
     * @return array
     */
    public static function getAllUserIdsAtGroupByUserId($iUserId)
    {
        /** @var CuserToGroup $obGroup */
        $obGroup = self::find()->where(['cuser_id' => $iUserId])->one();      //get group id for user
        if($obGroup)
        {
            return ArrayHelper::getColumn(self::find()->where(['group_id' => $obGroup->group_id])->all(),'cuser_id');
        }else{
            return [$iUserId];
        }
    }
}
