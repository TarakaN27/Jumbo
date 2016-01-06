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
use Yii;
class BUserSignupForm extends Model{

    public
        $fname,
        $lname,
        $mname,
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
            ['password_repeat','required'],
            [['fname','lname','mname'], 'string', 'min' => 2, 'max' => 255],
        ];
    }


    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [

            'username' => Yii::t('app/users', 'Username'),
            'password' => Yii::t('app/users', 'Password'),
            'fname' => Yii::t('app/users', 'First name'),
            'lname' => Yii::t('app/users', 'Last name'),
            'mname' => Yii::t('app/users', 'Midle name'),
            'password_repeat' => Yii::t('app/users','Password_repeat')
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
            $user->lname = $this->lname;
            $user->fname = $this->fname;
            $user->mname = $this->mname;
            $user->log_work_type = BUser::LOG_WORK_TYPE_DEFAULT;
            $user->setPassword($this->password);
            $user->generateAuthKey();
            $user->save();

            return $user;
        }

        return null;
    }
} 