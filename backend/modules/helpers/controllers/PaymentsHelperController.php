<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 17.2.16
 * Time: 10.17
 */

namespace backend\modules\helpers\controllers;


use backend\components\AbstractBaseBackendController;
use common\models\CrmTask;
use common\models\CrmTaskLogTime;
use common\models\CuserQuantityHour;
use common\models\ExchangeCurrencyHistory;
use common\models\Payments;
use common\models\Services;
use yii\db\Query;

class PaymentsHelperController extends AbstractBaseBackendController
{

	public function actionPaymentsQuantityHourRecalc()
	{
		$arPayments = Payments::find()->all();
		$arServIds = [];
		$arPayByUser = [];
		$arCurr = [];
		foreach($arPayments as $payment) {

			$arServIds [] = $payment->service_id;
		//	$arPayByUser[$payment->cuser_id] = $payment;
		}
		$arServIds = array_unique($arServIds);
		$arServices = Services::findAll(['id' => $arServIds]);

		$arServById = [];
		foreach($arServices as $serv)
			$arServById[$serv->id] = empty($serv->rate) ? \Yii::$app->config->get('qh_rate',0) : $serv->rate;

		$arPayInsert = [];
		/** @var Payments $pay */
		foreach($arPayments as $pay)
		{
			$date = date('Y-m-d',time());
			if(isset($arCurr[$date.'-'.$pay->currency_id]))
				$curr = $arCurr[$date.'-'.$pay->currency_id];
			else
			{
				$curr = ExchangeCurrencyHistory::getCurrencyInBURForDate($date,$pay->currency_id);
				$arCurr[$date.'-'.$pay->currency_id] = $curr;
			}

			$amount = $pay->pay_summ*$curr;

			$rate = isset($arServById[$pay->service_id]) ? $arServById[$pay->service_id] : NULL;

			if(!is_null($rate) && $rate > 0)
			{	if(isset($arPayByUser[$pay->cuser_id]))
					$arPayByUser[$pay->cuser_id] += round($amount/$rate,2);
				else
					$arPayByUser[$pay->cuser_id] = round($amount/$rate,2);
			}

		}



		$arLogTmp = (new Query())
			->select(['ct.cmp_id','t.spend_time'])
			->from(CrmTaskLogTime::tableName().' as t')
			->leftJoin(CrmTask::tableName().' as ct','ct.id = t.task_id')
			->where('ct.cmp_id IS NOT NULL')
			->all();

		$arLog = [];
		foreach($arLogTmp as $tmp )
			if(isset($arLog[$tmp['cmp_id']]))
				$arLog[$tmp['cmp_id']] += $tmp['spend_time'];
			else
				$arLog[$tmp['cmp_id']] = $tmp['spend_time'];

		$arInsert = [];
		foreach($arLog as $key => $log)
		{

			$hours = 0;
			if(isset($arPayByUser[$key]))
			{
				$hours = $arPayByUser[$key];
				unset($arPayByUser[$key]);
			}

			$arInsert [] = [
				'',
				$key,
				$hours,
				round($log/3600,2),
				time(),
				time()
			];
		}

		if(!empty($arPayByUser))
			foreach($arPayByUser as $key=>$hours)
			{
				$arInsert [] = [
					'',
					$key,
					$hours,
					0,
					time(),
					time()
				];
			}

		$postModel = new CuserQuantityHour();
		$tr = \Yii::$app->db->beginTransaction();
		try {
			//групповое добавление
			\Yii::$app->db->createCommand()
				->batchInsert($postModel::tableName(), $postModel->attributes(), $arInsert)
				->execute();
			$tr->commit();
		}catch (\Exception $e)
		{
			$tr->rollBack();
		}
	}

}