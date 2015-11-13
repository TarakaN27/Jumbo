<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 12.11.15
 * Time: 14.43
 */

namespace common\components\csda;

use yii\helpers\Json;

class CSDAPartner extends CSDAConnector
{
	/**
	 * @param $login
	 * @param $email
	 * @param $password
	 * @return bool|mixed
	 */
	public function createUser($login,$email,$password)
	{
		$url = $this->url.'/v1/partner/user-create?token='.urlencode($this->token);
		$result = $this->curlPostHelper($url,[
			'login' => $login,
			'email' => $email,
			'password' => $password
		]);

		return $result ? Json::decode($result) : FALSE;
	}

	/**
	 * @param $secretKey
	 * @return mixed
	 */
	public function deleteUser($secretKey)
	{
		$url = $this->url.'/v1/partner/user-delete?token='.urlencode($this->token);
		return $this->curlPostHelper($url,['psk' => $secretKey]);
	}

	/**
	 * @param $login
	 * @return bool|mixed
	 */
	public function loginIsBusy($login)
	{
		$url = $this->url.'/v1/partner/login-is-busy?token='.urlencode($this->token).'&login='.urlencode(trim($login));
		return $this->isBusyHelper($url,__METHOD__);
	}

	/**
	 * @param $email
	 * @return bool|mixed
	 */
	public function emailIsBusy($email)
	{
		$url = $this->url.'/v1/partner/email-is-busy?token='.urlencode($this->token).'&email='.urlencode(trim($email));
		return $this->isBusyHelper($url,__METHOD__);
	}
}