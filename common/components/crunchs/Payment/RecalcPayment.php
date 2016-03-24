<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 21.3.16
 * Time: 11.09
 * КОСТЫЛИ ДЛЯ ПЕРЕРАСЧЕТА ПЛАТЕЖЕЙ
 */

namespace common\components\crunchs\Payment;


use common\components\helpers\CustomHelper;
use common\components\payment\PaymentOperations;
use common\models\PaymentCondition;
use common\models\PaymentsCalculations;
use yii\web\ServerErrorHttpException;

class RecalcPayment
{
	public function recalculateWithSetConditions()
	{
		$ar_begin = [];
		$ar_except = [];
		$special = NULL;

		$arData = CustomHelper::csv_to_array(\Yii::getAlias('@backend/runtime/sverka_2.csv'));
		if(empty($arData))
			return FALSE;


		$arCondID = [];
		$arNewCond = [];
		foreach($arData as &$date)
		{
			if(!in_array($date['cond_id'],$ar_begin) && $date['cond_id'] != $special && !in_array($date['cond_id'],$ar_begin))
				$arCondID [] = (int)$date['cond_id'];
			$date['tax'] = str_replace(',','.',$date['tax']);
			$date['tax'] = is_numeric($date['tax']) ? (float)$date['tax'] : $date['tax'];
			$date['corr_factor'] = str_replace(',','.',$date['corr_factor']);
			$date['corr_factor'] = is_numeric($date['corr_factor']) ? (float)$date['corr_factor'] : $date['corr_factor'];
			$date['kommision'] = str_replace(',','.',$date['kommision']);
			$date['kommision'] = is_numeric($date['kommision']) ? (float)$date['kommision'] : $date['kommision'];

			$arNewCond[(int)$date['cond_id']] = $date;
		}


		$arPayCalc = PaymentsCalculations::find()->where(['pay_cond_id' => $arCondID])->all();

		$tr = \Yii::$app->db->beginTransaction();
		/** @var PaymentsCalculations $calc */
		foreach($arPayCalc as $calc)
		{
			if(!isset($arNewCond[$calc->pay_cond_id]))
				continue;

			$newCond = $arNewCond[$calc->pay_cond_id];

			$amount = floatval($calc->production) + floatval($calc->profit) + floatval($calc->tax);

			$corrfactor = is_numeric($newCond['corr_factor']) ? $newCond['corr_factor'] : $calc->cnd_corr_factor;
			$tax = is_numeric($newCond['tax']) ? $newCond['tax'] : $calc->cnd_tax;

			$commision = is_numeric($newCond['kommision']) ? $newCond['kommision'] : $calc->cnd_commission;
			$sale = $calc->cnd_sale;

			$obPO = new PaymentOperations($amount,$tax,$commision,$corrfactor,$sale,PaymentCondition::TYPE_USUAL,NULL);

			$result = $obPO->getFullCalculate();

			$calc->cnd_commission = $commision;
			$calc->cnd_sale = $sale;
			$calc->cnd_corr_factor = $corrfactor;
			$calc->cnd_tax = $tax;

			$calc->profit = $result['profit'];
			$calc->production = $result['production'];
			$calc->tax = $result['tax'];

			if(!$calc->save())
			{
				$tr->rollBack();
				throw new ServerErrorHttpException();
			}
		}

		unset($arPayCalc);
		/*
		foreach($ar_begin as $begin)
		{
			if(!isset($arNewCond[$begin]))
				continue;

			$newCond = $arNewCond[$begin];

			$arPayCalc = PaymentsCalculations::find()
				->select(['calc.*'])
				->alias('calc')
				->joinWith('payment pay')
				->where('pay.pay_date >= :begindate')
				->andwhere(['pay_cond_id' => $begin])
				->params([':begindate' => strtotime($newCond['bigin'].' 00:00:00')])
				->all();

			if(empty($arPayCalc))
				continue;

			foreach($arPayCalc as $calc)
			{
				$amount = floatval($calc->production) + floatval($calc->profit) + floatval($calc->tax);

				$corrfactor = is_numeric($newCond['corr_factor']) ? $newCond['corr_factor'] : $calc->cnd_corr_factor;
				$tax = is_numeric($newCond['tax']) ? $newCond['tax'] : $calc->cnd_tax;

				$commision = is_numeric($newCond['kommision']) ? $newCond['kommision'] : $calc->cnd_commission;
				$sale = $calc->cnd_sale;

				$obPO = new PaymentOperations($amount,$tax,$commision,$corrfactor,$sale,PaymentCondition::TYPE_USUAL,NULL);

				$result = $obPO->getFullCalculate();

				$calc->cnd_commission = $commision;
				$calc->cnd_sale = $sale;
				$calc->cnd_corr_factor = $corrfactor;
				$calc->cnd_tax = $tax;

				$calc->profit = $result['profit'];
				$calc->production = $result['production'];
				$calc->tax = $result['tax'];

				if(!$calc->save())
				{
					$tr->rollBack();
					throw new ServerErrorHttpException();
				}
			}
		}

		if(isset($arPayCalc))
			unset($arPayCalc);

		$arPayCalc =  PaymentsCalculations::find()
			->select(['calc.*'])
			->alias('calc')
			->joinWith('payment pay')
			->where('pay.pay_date >= :begindate')
			->andwhere(['pay_cond_id' => 112])
			->params([':begindate' => strtotime('17.02.2016 00:00:00')])
			->all();

		foreach($arPayCalc as $calc)
		{
			$amount = floatval($calc->production) + floatval($calc->profit) + floatval($calc->tax);

			$corrfactor = $calc->cnd_corr_factor;
			$tax = 0;

			$commision = 50;
			$sale = $calc->cnd_sale;

			$obPO = new PaymentOperations($amount,$tax,$commision,$corrfactor,$sale,PaymentCondition::TYPE_USUAL,NULL);

			$result = $obPO->getFullCalculate();

			$calc->cnd_commission = $commision;
			$calc->cnd_sale = $sale;
			$calc->cnd_corr_factor = $corrfactor;
			$calc->cnd_tax = $tax;

			$calc->profit = $result['profit'];
			$calc->production = $result['production'];
			$calc->tax = $result['tax'];

			if(!$calc->save())
			{
				$tr->rollBack();
				throw new ServerErrorHttpException();
			}
		}

		$arPayCalc =  PaymentsCalculations::find()
			->select(['calc.*'])
			->alias('calc')
			->joinWith('payment pay')
			->where('pay.pay_date < :begindate')
			->andwhere(['pay_cond_id' => 112])
			->params([':begindate' => strtotime('17.02.2016 00:00:00')])
			->all();

		foreach($arPayCalc as $calc)
		{
			$amount = floatval($calc->production) + floatval($calc->profit) + floatval($calc->tax);

			$corrfactor = $calc->cnd_corr_factor;
			$tax = 0;

			$commision = 25;
			$sale = $calc->cnd_sale;

			$obPO = new PaymentOperations($amount,$tax,$commision,$corrfactor,$sale,PaymentCondition::TYPE_USUAL,NULL);

			$result = $obPO->getFullCalculate();

			$calc->cnd_commission = $commision;
			$calc->cnd_sale = $sale;
			$calc->cnd_corr_factor = $corrfactor;
			$calc->cnd_tax = $tax;

			$calc->profit = $result['profit'];
			$calc->production = $result['production'];
			$calc->tax = $result['tax'];

			if(!$calc->save())
			{
				$tr->rollBack();
				throw new ServerErrorHttpException();
				}
		}


		if(isset($newCond))
			unset($newCond);
		$arConds = PaymentCondition::find()->where(['id' => array_keys($arNewCond)])->all();
		foreach($arConds as $conds)
		{
			if(!isset($arNewCond[$conds->id]))
				continue;

			$newCond = $arNewCond[$conds->id];

			if(is_numeric($newCond['tax']))
				$conds->tax = $newCond['tax'];
			if(is_numeric($newCond['kommision']))
				$conds->commission = $newCond['kommision'];
			if(is_numeric($newCond['corr_factor']))
				$conds->corr_factor = $newCond['corr_factor'];


			if(empty($conds->type))
				$conds->type = PaymentCondition::TYPE_USUAL;

			if(!$conds->save())
			{
				$tr->rollBack();
				throw new ServerErrorHttpException();
			}

		}
*/

		$tr->commit();

		echo 'done';
		return true;

	}
}