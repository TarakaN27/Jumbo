<?php

namespace common\models;

use backend\models\BUser;
use Yii;

/**
 * This is the model class for table "{{%buser_invite_code}}".
 *
 * @property integer $id
 * @property string $code
 * @property string $email
 * @property integer $user_type
 * @property integer $buser_id
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 */
class BuserInviteCode extends AbstractActiveRecord
{
    CONST
        NORMAL = 0,
        BROKEN = 1;

    protected
        $blockBeforeSave = FALSE;

    /**
     * @return array
     */
    public static function getStatusArr()
    {
        return [
            self::NORMAL => Yii::t('app/users','Normal'),
            self::BROKEN => Yii::t('app/users','Broken')
        ];
    }

    /**
     * @return string
     */
    public function getStatusStr()
    {
        $tmp = self::getStatusArr();
        return isset($tmp[$this->status]) ? $tmp[$this->status] : 'N/A';
    }

    /**
     * @return string
     */
    public function getUserTypeStr()
    {
        $tmp = BUser::getRoleArr();
        return isset($tmp[$this->user_type]) ? $tmp[$this->user_type] : 'N/A';
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%buser_invite_code}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_type','buser_id','email'],'required'],
            [['user_type', 'buser_id', 'status', 'created_at', 'updated_at'], 'integer'],
            [['code', 'email'], 'string', 'max' => 255],
            [['email'],'email'],
            ['email','unique','targetClass' => BUser::className(),'targetAttribute' => 'email',
             'message' => Yii::t('app/common','User with this email already registered.')
            ],
            ['user_type','in', 'range' => array_keys(BUser::getRoleArr())]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/users', 'ID'),
            'code' => Yii::t('app/users', 'Code'),
            'email' => Yii::t('app/users', 'Email'),
            'user_type' => Yii::t('app/users', 'User Type'),
            'buser_id' => Yii::t('app/users', 'Buser ID'),
            'status' => Yii::t('app/users', 'Status'),
            'created_at' => Yii::t('app/users', 'Created At'),
            'updated_at' => Yii::t('app/users', 'Updated At'),
        ];
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if(parent::beforeSave($insert))
        {
            if($this->blockBeforeSave)
                return TRUE;

            if(self::find()->where(['email'=>$this->email,'status' => self::NORMAL])->exists())
            {
                if(!self::updateAll(['status' => self::BROKEN],
                    'email = :email AND status = :status',
                    [':email'=>$this->email,':status' => self::NORMAL])
                )
                    return FALSE;
            }
            $this->code = $this->generateInviteToken();
            $this->status = self::NORMAL;
            return TRUE;
        }
        return FALSE;
    }

    /**
     * @return bool
     */
    public function resend()
    {
        $this->blockBeforeSave = TRUE;
        $this->code = $this->generateInviteToken();
        $this->status = self::NORMAL;
        if($this->save())
            return $this->sendEmail();
        return FALSE;
    }

    /**
     * @return string
     */
    private function generateInviteToken()
    {
        return Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * @param $token
     * @return null|static
     */
    public static function findByToken($token)
    {
        if (!self::isTokenValid($token)) {
            return null;
        }
        return static::findOne([
            'code' => $token,
            'status' =>self::NORMAL
        ]);
    }

    /**
     * @param $token
     * @return bool
     */
    public static function isTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        $parts = explode('_', $token);
        $timestamp = (int) end($parts);
        return $timestamp + $expire >= time();
    }

    public function sendEmail()
    {
        return \Yii::$app->mailer->compose('sendBUserInvite-html',['code' => $this->code])
            ->setFrom([\Yii::$app->params['supportEmail'] => \Yii::$app->name . ' robot'])
            ->setTo($this->email)
            ->setSubject('User invite for ' . \Yii::$app->name)
            ->send();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBuser()
    {
        return $this->hasOne(BUser::className(),['id' => 'buser_id']);
    }
}
