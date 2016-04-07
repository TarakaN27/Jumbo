<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 5.4.16
 * Time: 16.27
 */

namespace common\models\managers;


use common\models\ExchangeRates;

class ExchangeRatesManager extends ExchangeRates
{
	/**
	 * Валюты для виджета
	 * @return mixed
	 */
	public static function getCurrencyForWidget()
	{
		return ExchangeRates::find()->forWidget()->orderBy(['code' => SORT_ASC])->all();
	}

}