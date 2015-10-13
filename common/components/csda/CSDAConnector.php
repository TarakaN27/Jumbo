<?php
namespace common\components\csda;
use linslin\yii2\curl\Curl;
use yii\base\Exception;
use yii\helpers\Json;

/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 1.10.15
 * Time: 12.20
 */
class CSDAConnector
{
    protected
        $url,
        $_error,
        $token;

    public function __construct()
    {
        $this->token = \Yii::$app->params['csdaToken'];
        $this->url = \Yii::$app->params['csdaUrlApi'];
    }

    /**
     * @param $login
     * @return bool|mixed
     */
    public function loginIsBusy($login)
    {
        $url = $this->url.'/v1/users/login-is-busy?token='.urlencode($this->token).'&login='.urlencode(trim($login));
        return $this->isBusyHelper($url,__METHOD__);
    }

    /**
     * @param $email
     * @return bool|mixed
     */
    public function emailIsBusy($email)
    {
        $url = $this->url.'/v1/users/email-is-busy?token='.urlencode($this->token).'&email='.urlencode(trim($email));
        return $this->isBusyHelper($url,__METHOD__);
    }

    /**
     * @param $url
     * @param $method
     * @return bool|mixed
     */
    protected function isBusyHelper($url,$method)
    {
        try {
            $obCurl = new Curl();
            $iRes = $obCurl->get($url);
        }catch (Exception $e)
        {
            $this->_error[] = [
                'method' => $method,
                'error' => $e->getCode().' '.$e->getMessage(),
            ];
            return FALSE;
        }
        if($iRes)
            return Json::decode($iRes);
        else{
            $this->_error[] = [
                'method' => $method,
                'error' => $obCurl->responseCode,
            ];
            return FALSE;
        }
    }

    /**
     * @param $url
     * @param array $params
     * @return mixed
     */
    protected function curlPostHelper($url,$params = [])
    {
        $obCurl = new Curl();
        $result = $obCurl->setOption(CURLOPT_POSTFIELDS, http_build_query($params))->post($url);
        unset($obCurl);
        return $result;
    }

    /**
     * @param $login
     * @param $email
     * @param $password
     * @return bool|mixed
     */
    public function createUser($login,$email,$password)
    {
        $url = $this->url.'/v1/users/user-create?token='.urlencode($this->token);
        $result = $this->curlPostHelper($url,[
            'login' => $login,
            'email' => $email,
            'password' => $password
        ]);

        return $result ? Json::decode($result) : FALSE;
    }

    public function deleteUser($secretKey)
    {
        $url = $this->url.'/v1/users/user-delete?token='.urlencode($this->token);
        return $this->curlPostHelper($url,['secret_key' => $secretKey]);
    }
}