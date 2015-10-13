<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 1.10.15
 * Time: 12.09
 */
namespace backend\modules\users\models;

use common\components\csda\CSDAConnector;
use common\models\CuserExternalAccount;
use yii\base\Exception;
use yii\base\Model;
use Yii;
use yii\web\NotFoundHttpException;

class CreateExternalAccount extends Model
{
    public
        $password,
        $email,
        $type,
        $passwordConfirm,
        $cUserID,
        $login;

    protected
        $externalError;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['type','cUserID'], 'integer'],
            ['type', 'in', 'range' => array_keys(CuserExternalAccount::getTypeArr())],
            ['login', 'filter', 'filter' => 'trim'],
            ['login', 'required'],
            ['login', 'match', 'pattern' => '#^[\w_-]+$#i'],
            ['login', 'string', 'min' => 2, 'max' => 255],

            ['email', 'email'],

            [['password', 'passwordConfirm', 'email','cUserID'], 'required'],
            [['password', 'passwordConfirm'], 'string', 'min' => 6],

            [['passwordConfirm'], 'compare', 'compareAttribute' => 'password',
                'message' => Yii::t('app/users', 'Passwords do not match')],

            ['login', 'externalValidateLogin'],
            ['email', 'externalValidateEmail']
        ];
    }

    /**
     * Проверяем не занят ли логин во внешней системе
     * @param $attribute
     * @param $params
     */
    public function externalValidateLogin($attribute, $params)
    {
        switch ($this->type) {
            case CuserExternalAccount::TYPE_CSDA:
                $obCsda = new CSDAConnector();
                $isBusy = $obCsda->loginIsBusy($this->login);
                $this->extrValHelper($isBusy,$attribute,'login_is_busy','login');
                break;
            default:
                $this->addError($attribute, Yii::t('app/users', 'Unknown external system type for validate login.'));
                break;
        }
    }

    /**
     * Проверяем не занят ли email во  внешней системе
     * @param $attribute
     * @param $params
     */
    public function externalValidateEmail($attribute, $params)
    {
        switch ($this->type) {
            case CuserExternalAccount::TYPE_CSDA:
                $obCsda = new CSDAConnector();
                $isBusy = $obCsda->emailIsBusy($this->email);
                $this->extrValHelper($isBusy,$attribute,'email_is_busy','email');
                break;
            default:
                $this->addError($attribute, Yii::t('app/users', 'Unknown external system type for validate email.'));
                break;
        }
    }

    /**
     * @param $isBusy
     * @param $attribute
     * @param $extProp
     * @param $propName
     */
    protected function extrValHelper($isBusy,$attribute,$extProp,$propName)
    {
        if (!$isBusy)
            $this->addError($attribute, Yii::t('app/users', 'Can not connect to external system.'));

        if (!isset($isBusy[$extProp]))
            $this->addError($attribute, Yii::t('app/users', 'External system return empty response.'));

        if ($isBusy[$extProp])
            $this->addError($attribute, Yii::t('app/users', 'This '.$propName.' has already taken in external system.'));
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'login' => Yii::t('app/users','Login'),
            'password' => Yii::t('app/users','Password'),
            'passwordConfirm' => Yii::t('app/users','Password confirm'),
        ];
    }

    /**
     * Выполняем запрос на создание пользователя во внешеней системе
     * @return bool
     * @throws NotFoundHttpException
     */
    public function makeRequest()
    {
        $bResult = FALSE;

        switch($this->type)
        {
            case CuserExternalAccount::TYPE_CSDA:
                $obCsda = new CSDAConnector();
                $extUser = $obCsda->createUser($this->login,$this->email,$this->password);

                if($extUser)
                {
                    try {
                        /** @var CuserExternalAccount $obExt */
                        $obExt = new CuserExternalAccount();
                        $obExt->type = $this->type;
                        $obExt->login = $this->login;
                        $obExt->password = $this->password;
                        $obExt->secret_key = $extUser['secretKey'];
                        $obExt->cuser_id = $this->cUserID;
                        if(!$obExt->save())
                        {
                            $obCsda->deleteUser($extUser['secretKey']);
                            return FALSE;

                        }else{
                            return TRUE;
                        }
                    }catch (Exception $e)
                    {
                        $obCsda->deleteUser($extUser['secretKey']);
                    }
                }
                break;
            default:
                throw new NotFoundHttpException('Unknown external system type.');
                break;
        }

        return FALSE;
    }

    /**
     * @return bool
     */
    public function hasExternalError()
    {
        return empty($this->externalError);
    }

    /**
     * @return mixed
     */
    public function getExternalError()
    {
        return $this->externalError;
    }



}