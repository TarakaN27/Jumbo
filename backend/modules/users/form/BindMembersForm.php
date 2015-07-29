<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 29.07.15
 */

namespace backend\modules\users\form;

use backend\models\BUser;
use yii\base\InvalidParamException;
use yii\base\Model;
use yii\web\NotFoundHttpException;

class BindMembersForm extends Model{

    public
        $userID,
        $members;

    protected
        $userModel;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['members','safe'],
            ['members', 'default', 'value' => -1],
        ];
    }

    public function attributeLabels()
    {
        return [
            'members' => \Yii::t('app/users','Bind members'),
        ];
    }

    /**
     * @throws \yii\base\InvalidParamException
     * @throws \yii\web\NotFoundHttpException
     */
    public function init()
    {
        parent::init();
        if(empty($this->userID))
            throw new InvalidParamException("User id is empty. Please set id.");

        $this->userModel = BUser::findOne($this->userID);
        if(empty($this->userModel))
            throw new NotFoundHttpException('BUser not found, please check id.');

        $obMembers = $this->userModel->bindMembers;
        if(is_array($obMembers))
            foreach($obMembers as $memb)
                $this->members[] = $memb->id;
    }

    /**
     * @return bool
     */
    public function makeRequest()
    {
        $trans = \Yii::$app->db->beginTransaction();
        try{
            $obMembers = $this->userModel->bindMembers; //получаем пользователей, которые уже связаны
            if(is_array($obMembers))
            {
                foreach($obMembers as $memb)
                    $this->userModel->unlink('bindMembers',$memb,TRUE); //удаляем старые связи
            }

            if(!is_array($this->members) && $this->members == -1)
                return TRUE;

            $obNewMembers = BUser::find()->where(['id' => $this->members])->all();

            foreach($obNewMembers as $obNMemb)
                $this->userModel->link('bindMembers',$obNMemb); //пишем новые связи.

            $trans->commit();
            return TRUE;
        }catch (\Exception $e)
        {
            $trans->rollBack();
            return FALSE;
        }
    }

} 