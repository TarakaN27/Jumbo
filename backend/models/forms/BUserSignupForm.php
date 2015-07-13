<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 13.07.15
 */

namespace backend\models\forms;


use backend\models\BUser;
use common\models\BuserInviteCode;
use yii\base\Model;

class BUserSignupForm extends Model{

    public
        $role,
        $username,
        $email,
        $password,
        $obInvite,
        $password_repeat;


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['role','integer'],
            ['username', 'filter', 'filter' => 'trim'],
            ['username', 'required'],
            ['username', 'unique',
             'targetClass' => BUser::className(),
             'message' => 'This username has already been taken.'],
            ['username', 'string', 'min' => 2, 'max' => 255],

            ['email', 'filter', 'filter' => 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'unique', 'targetClass' => BUser::className(), 'message' => 'This email address has already been taken.'],

            ['password', 'required'],
            ['password', 'string', 'min' => 6],

            ['password_repeat', 'compare', 'compareAttribute' => 'password'],
            ['password_repeat','required']
        ];
    }

    /**
     * Signs user up.
     *
     * @return User|null the saved model or null if saving fails
     */
    public function signup()
    {
        if ($this->validate()) {
            $user = new BUser();
            $user->username = $this->username;
            $user->email = $this->email;
            $user->role = $this->role;
            $user->status = BUser::STATUS_ACTIVE;
            $user->setPassword($this->password);
            $user->generateAuthKey();
            $user->save();

            return $user;
        }

        return null;
    }
} 