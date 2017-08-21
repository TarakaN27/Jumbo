<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 5.4.16
 * Time: 16.27
 */

namespace common\models\managers;


use common\models\ExchangeCurrencyHistory;
use common\models\ExchangeRates;

class ExchangeRatesManager extends ExchangeRates
{
	/**
	 * Валюты для виджета
	 * @return mixed
	 */
	public static function getCurrencyForWidget($date = null)
	{
        return ExchangeCurrencyHistory::find()->select([
            ExchangeRates::tableName().'.name',
            ExchangeRates::tableName().'.code',
            ExchangeCurrencyHistory::tableName().'.rate_nbrb',
            ExchangeCurrencyHistory::tableName().'.updated_at',
        ])
            ->leftJoin(ExchangeRates::tableName(), ExchangeRates::tableName().'.id = '.ExchangeCurrencyHistory::tableName().'.currency_id')
            ->andWhere([ExchangeCurrencyHistory::tableName().'.date' => $date])
            ->andWhere([ExchangeRates::tableName().'.show_at_widget' => ExchangeRates::YES])
            ->orderBy(['code' => SORT_ASC])->asArray()->all();
	}

}