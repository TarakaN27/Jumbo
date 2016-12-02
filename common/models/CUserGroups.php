<?php

namespace common\models;

use Yii;
use yii\base\Exception;

/**
 * This is the model class for table "{{%c_user_groups}}".
 *
 * @property integer $id
 * @property string $name
 * @property integer $created_at
 * @property integer $updated_at
 */
class CUserGroups extends AbstractActiveRecord
{
    public
        $cuserIds = [];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%c_user_groups}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name','cuserIds'],'required'],
            [['created_at', 'updated_at'], 'integer'],
            [['name'], 'string', 'max' => 255],
            ['cuserIds', 'each', 'rule' => ['integer']]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/users', 'ID'),
            'name' => Yii::t('app/users', 'Name'),
            'created_at' => Yii::t('app/users', 'Created At'),
            'updated_at' => Yii::t('app/users', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCUser()
    {
        return $this->hasMany(CuserToGroup::className(),['group_id' => 'id']);
    }

    /**
     * Получаем пользователей(объекты),которые помогают
     * @return \yii\db\ActiveQuery
     */
    public function getCuserObjects()
    {
        return $this->hasMany(CUser::className(), ['id' => 'cuser_id'])->viaTable(CuserToGroup::tableName(), ['group_id' => 'id']);
    }

    /**
     * @param bool|FALSE $unlinkAll
     * @return bool
     * @throws \yii\db\Exception
     */
    public function saveWithCUser($unlinkAll = FALSE)
    {
        $tr = Yii::$app->db->beginTransaction();
        try{
            /** @var self $model */
            if($this->save())
            {
                $oldUser = false;
                $oldCusers = $this->cuserObjects;
                // найдём пользователей в группе чтобы проставить дату и менеджера продажи
                if($oldCusers){
                    $oldUser = $oldCusers[0];
                }
                if($unlinkAll)
                    $this->unlinkAll('cuserObjects',TRUE);
                $arCusers = CUser::findAll(['id' => $this->cuserIds]);
                //если в группе не было клиентов, то среди выбранных юзеров найдём раннюю дату
                if(!$oldUser){
                    $saleDate = time();
                    foreach($arCusers as $cuser){
                        if($cuser->sale_date && $cuser->sale_date<$saleDate){
                            $saleDate = $cuser->sale_date;
                            $oldUser = $cuser;
                        }
                    }
                }

                if(!empty($arCusers))
                    foreach($arCusers as $obCuser) {
                        if($oldUser){
                            $obCuser->sale_manager_id = $oldUser->sale_manager_id;
                            $obCuser->sale_date = $oldUser->sale_date;
                            $obCuser->save();
                        }
                        $this->link('cuserObjects', $obCuser);
                    }

                $tr->commit();
                return TRUE;
            }else
                return FALSE;
        }catch (Exception $e){
            $tr->rollBack();
            return FALSE;
        }
    }
}
