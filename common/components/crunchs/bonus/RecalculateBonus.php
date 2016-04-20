<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 1.4.16
 * Time: 16.18
 */

namespace common\components\crunchs\bonus;


use common\components\payment\PaymentBonusBehavior;
use common\models\BUserBonus;
use common\models\Payments;
use common\models\PaymentsSale;
use common\components\helpers\CustomHelper;
use common\models\BonusScheme;
use common\models\BonusSchemeExceptCuser;
use common\models\BonusSchemeServiceHistory;
use common\models\BonusSchemeToBuser;
use common\models\BonusSchemeToCuser;
use common\models\ExchangeCurrencyHistory;
use common\models\managers\PaymentsManager;
use common\models\PaymentCondition;
use common\models\PaymentsCalculations;
use yii\web\Controller;
use yii\web\ServerErrorHttpException;
use Yii;

class RecalculateBonus
{
	/**
	 * @return bool
	 * @throws \yii\web\NotFoundHttpException
	 */
	public function run()
	{
		$arPaymentsTmp = Payments::find()->all();
		$arPayments = [];
		foreach ($arPaymentsTmp as $payment)
			$arPayments[$payment->id] = $payment;

		$arSales = PaymentsSale::find()->all();

		$obCount = new PaymentBonusBehavior();

		/** @var PaymentsSale $sale */
		foreach($arSales as $key => $sale)
		{
			if(!isset($arPayments[$sale->payment_id]))
				continue;

			/** @var Payments $model */
			$model = $arPayments[$sale->payment_id];
			$model->saleUser = $sale->buser_id;
			$model->isSale = TRUE;

			$obCount->countingSimpleBonus($model);
			$obCount->countingComplexBonus($model);
			unset($arPayments[$sale->payment_id]);
		}

		foreach ($arPayments as $pay)
		{
			$obCount->countingSimpleBonus($pay,BonusScheme::BASE_PAYMENT);
			$obCount->countingComplexBonus($pay,BonusScheme::BASE_PAYMENT);
		}
		echo 'done'.PHP_EOL;
		return TRUE;
	}

	/**
	 * @return bool
	 */
	public function recalculatePartnerBonus()
	{
		$arPaymentsTmp = Payments::find()->all();
		$obCount = new PaymentBonusBehavior();

		foreach ($arPaymentsTmp as $payment)
			$obCount->countingPartnerBonus($payment);

		echo 'done'.PHP_EOL;
		return TRUE;
	}
}