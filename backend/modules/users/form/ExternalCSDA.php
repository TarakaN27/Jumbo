<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 12.11.15
 * Time: 16.26
 */

namespace backend\modules\users\form;


use common\components\csda\CSDAPartner;
use common\models\Partner;
use yii\base\Model;
use Yii;
use Exception;

class ExternalCSDA extends Model
{

	public
		$partnerID,
		$username,
		$email,
		$password,
		$passwordRepeat;

	protected
		$_error;

	public function rules()
	{
		return [
			//партнер
			['partnerID', 'required'],
			['partnerID', 'integer'],

			//имя пользователя
			['username', 'required'],
			['username', 'match', 'pattern' => '#^[\w_-]+$#i'],
			['username', 'string', 'min' => 2, 'max' => 255],

			//емаил
			['email', 'required'],
			['email', 'email'],
			['email', 'string', 'max' => 255],

			//пароль
			[['password','passwordRepeat'], 'required'],
			[['password','passwordRepeat'], 'string', 'min' => 6],
			['passwordRepeat', 'compare', 'compareAttribute' => 'password'],

			//спец проверки
			['username','usernameIsBusy'],
			['email','emailIsBusy']
		];
	}


	public function attributeLabels()
	{
		return [
			'username' => Yii::t('app/users', 'Username'),
			'password' => Yii::t('app/users', 'Password'),
			'passwordRepeat' => Yii::t('app/users', 'passwordRepeat'),
			'email' => Yii::t('app/users', 'Email'),
		];
	}

	/**
	 * @param $attribute
	 * @param $params
	 */
	public function usernameIsBusy($attribute, $params)
	{
		$obCSDA = new CSDAPartner();
		$res = $obCSDA->loginIsBusy($this->username);
		$this->extrValHelper($res,$attribute,'login_is_busy','username');
	}

	/**
	 * @param $attribute
	 * @param $params
	 */
	public function emailIsBusy($attribute,$params)
	{
		$obCSDA = new CSDAPartner();
		$res = $obCSDA->emailIsBusy($this->email);
		$this->extrValHelper($res,$attribute,'email_is_busy','email');
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
	 * @return bool
	 */
	public function makeRequest()
	{
		$obCsda = new CSDAPartner();
		$extUser = $obCsda->createUser($this->username,$this->email,$this->password);
		if($extUser && isset($extUser['psk']))
		{
			try {
				$obPartner = Partner::findOne($this->partnerID);
				if(!$obPartner) {
					$obCsda->deleteUser($extUser['psk']);
					$this->_error [] = 'Partner not found';
					return FALSE;
				}

				$obPartner->psk = $extUser['psk'];

				if(!$obPartner->save())
				{
					$obCsda->deleteUser($extUser['psk']);
					$this->_error  = $obPartner->getErrors();
					return FALSE;
				}else{
					return TRUE;
				}
			}catch (Exception $e)
			{
				$obCsda->deleteUser($extUser['psk']);
				$this->_error  [] = $e->getCode().' '.$e->getMessage();
				return FALSE;
			}
		}
		$this->_error  [] = 'Empty psk';
		return FALSE;
	}

	public function getSPErrors()
	{
		return $this->_error;
	}
}