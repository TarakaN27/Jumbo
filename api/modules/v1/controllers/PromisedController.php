<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 20.10.15
 * Time: 16.55
 */
namespace api\modules\v1\controllers;

use api\components\AbstractActiveActionREST;
use common\components\payment\PromisedPaymentHelper;
use common\models\PromisedPayment;
use common\models\Services;
use Yii;
use common\models\CuserExternalAccount;
use common\models\CUser;
use yii\base\Exception;

class PromisedController extends AbstractActiveActionREST
{
	public $modelClass = 'common\models\PromisedPayment';

	/**
	 * ЗАкроем все остальные экшены
	 * @return array
	 */
	public function actions()
	{
		return [];
	}

	/**
	 * @return array
	 * @throws \yii\web\ForbiddenHttpException
	 */
	public function actionPromisedPayment()
	{
		$sk = Yii::$app->request->post('sk');

		$obUser = CuserExternalAccount::findOneBySecretKeyCahed($sk);   //находим ID пользователей по SK
		if(!$obUser)
			return ['data' => NULL,'error'=>'User not found'];

		$obPayList = PromisedPayment::getPaymentListForUserCached($obUser->id); //получаем обещанные платежи для пользователя

		$arResult = ['error' => NULL,'data' => []];

		foreach($obPayList as $pay)
			$arResult['data'][] = [
				'service_id' => $pay->service_id,
				'service_name' => is_object($obServ = $pay->service) ? $obServ->name : 'N/A',
				'amount' => $pay->amount,
				'created_at' => $pay->created_at,
				'overdue_day' => PromisedPayment::OVERDUE_DAYS
			];

		return $arResult;
	}

	/**
	 * @return array
	 * @throws \yii\web\ForbiddenHttpException
	 */
	public function actionGetServices()
	{
		$sk = Yii::$app->request->post('sk');

		/** @var CUser $obUser */
		$obUser = CuserExternalAccount::findOneBySecretKeyCahed($sk);   //находим ID пользователей по SK
		if(!$obUser)
			return ['data' => NULL,'error'=>'User not found'];

		try{
			$arServices = Services::getServicesMap();   // получаем все услуги

			$obPPH = new PromisedPaymentHelper($obUser->id);
			$arLimits = $obPPH->getMaxAmountForServices(array_keys($arServices));   // получаем лимиты для услуг
			$arResult = [];

			foreach($arServices as $key => $serv)
			{
				if(isset($arLimits[$key]))
					$arResult [] = [
						'serviceID' => $key,
						'serviceName' => $serv,
						'limit' => $arLimits[$key]
					];
			}

			return ['data' => $arResult,'error'=>NULL];
		}catch (Exception $e)
		{
			return ['data' => NULL,'error'=>$e->getCode().';'.$e->getMessage()];
		}
	}

	/**
	 * @return array
	 */
	public static function actionCreate()
	{
		$sk = Yii::$app->request->post('sk');
		$iServiceID = Yii::$app->request->post('serviceID');
		$iAmount = Yii::$app->request->post('amount');

		if(empty($iServiceID) || empty($iAmount))
			return ['data' => NULL,'error'=>'Service ID and amount is required'];

		/** @var CUser $obUser */
		$obUser = CuserExternalAccount::findOneBySecretKeyCahed($sk);   //находим ID пользователей по SK
		if(!$obUser)
			return ['data' => NULL,'error'=>'User not found'];

		if(PromisedPayment::isPaymentExist($obUser->id,$iServiceID))    // проверяем нет ли не погашенного платежа по услуге
			return ['data' => NULL,'error' => 'Promised payment already exist for this service'];

		$obPPH = new PromisedPaymentHelper($obUser->id);    //проверим не превышена ли сумма
		if(!$obPPH->isAllowedAmount($obUser->id,(int)$iAmount,(int)$iServiceID))
			return ['data' => NULL,'error' => 'Not allowed amount'];

		$obPP = new PromisedPayment();  //добавим платеж
		$obPP->amount = $iAmount;
		$obPP->service_id = $iServiceID;
		$obPP->cuser_id = $obUser->id;
		$obPP->paid =PromisedPayment::NO;

		if($obPP->save())
			return ['data' => $obPP->id,'error' => NULL];

		return ['data' => NULL,'error' => implode(';',$obPP->getErrors())];
	}


}