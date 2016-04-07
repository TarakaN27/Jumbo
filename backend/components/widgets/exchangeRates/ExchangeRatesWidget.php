<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 5.4.16
 * Time: 16.32
 */

namespace backend\components\widgets\exchangeRates;


use backend\components\widgets\exchangeRates\assets\ExchangeRatesAssets;
use yii\base\Widget;

class ExchangeRatesWidget extends Widget
{
	public function run()
	{
		$this->registerAssets();
		return $this->render('exchange_rates');
	}

	/**
	 *
	 */
	public function registerAssets()
	{
		$view = $this->getView();
		ExchangeRatesAssets::register($view);
	}
}