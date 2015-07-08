<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 02.07.15
 */

namespace backend\modules\users\models;


use backend\models\BUser;
use yii\base\Model;
use Yii;
use yii\web\NotFoundHttpException;

class ChangePasswordBUserForm extends Model{

    public
        $password,
        $repeatPass,
        $userID;


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['password', 'required'],
            ['password', 'string', 'min' => 6],
            ['repeatPass', 'required'],
            ['repeatPass', 'string', 'min' => 6],

            [['repeatPass'], 'compare', 'compareAttribute' => 'password'],
		   // ['password', 'compare'],//password_repeat
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'password' => Yii::t('app/users', 'Password'),
            'repeatPass' => Yii::t('app/users', 'Password_repeat'),
        ];
    }

    /**
     * MAke request
     * @return bool
     */
    public function makeRequest()
    {
        /** @var BUser $model */
        $model = $this->getUser();
        $model->setPassword($this->password);
        return $model->save();
    }

    /**
     * @return null|static
     * @throws \yii\web\NotFoundHttpException
     */
    private function getUser()
    {
        $model = BUser::findOne($this->userID);
        if(empty($model))
            throw new NotFoundHttpException('User not found');
        //$model->setScenario(BUser::SCENARIO_CHANGE_PASSWORD);
        return $model;
    }

} 