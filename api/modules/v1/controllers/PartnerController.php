<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 11.11.15
 * Time: 11.55
 */

namespace api\modules\v1\controllers;

use api\components\AbstractActiveActionREST;
use common\models\managers\PartnerWithdrawalManager;
use common\models\Partner;
use common\models\PartnerPurse;
use yii\base\InvalidParamException;
use yii\web\NotFoundHttpException;

class PartnerController extends AbstractActiveActionREST
{
	public $modelClass = 'common\models\PartnerPurse';

	/**
	 * Кошелек партнера
	 * @return array
	 * @throws NotFoundHttpException
	 */
	public function actionPurse()
	{
		$psk = \Yii::$app->request->post('psk');
		if(!$psk)
			throw new InvalidParamException('psk must be set');

		$obPrt = Partner::getPartnerByPsk($psk);    //находим партнера
		if(!$obPrt)
			throw new NotFoundHttpException('Partner not found');

		$obPurse = PartnerPurse::getPurse($obPrt->id); // получаем кошелек
		if(!$obPurse)
			throw new NotFoundHttpException('Partner purse not found');

		$aWP = PartnerWithdrawalManager::getAmountByPeriod($obPrt->id); //получаем сумму выводов за текущий месяц(период)

		return [
			'acts' => $obPurse->acts,
			'payments' => $obPurse->payments,
			'amount' => $obPurse->amount,
			'awp' => $aWP
		];
	}

}