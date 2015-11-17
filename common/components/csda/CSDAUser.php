<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 17.11.15
 * Time: 11.03
 */

namespace common\components\csda;

use common\models\Acts;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\helpers\Html;

class CSDAUser extends CSDAConnector
{
	CONST
		TYPE_NEW_ACT = 1;

	/**
	 * @param Acts $act
	 * @param $sk
	 * @return bool|mixed
	 */
	public function sentNotificationNewAct(Acts $act,$sk)
	{
		$url = $this->url.'/v1/users/add-notification?token='.urlencode($this->token);

		$parse = parse_url(Url::home(true));
		$actLink = $parse['scheme'].'://'.$parse['host'].'/site/get-act-pdf?ask='.$act->ask;
		$result = $this->curlPostHelper($url,[
			'secret_key' => $sk,
			'type' => self::TYPE_NEW_ACT,
			'msg' => 'Акт за номером №'.$act->act_num.' от '.$act->act_date.' доступен по ссылке '.Html::a(Html::encode($actLink), $actLink)
		]);

		return $result ? Json::decode($result) : FALSE;
	}

}