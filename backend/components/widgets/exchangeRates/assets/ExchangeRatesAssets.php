<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 5.4.16
 * Time: 16.37
 */

namespace backend\components\widgets\exchangeRates\assets;


use yii\web\AssetBundle;

class ExchangeRatesAssets extends AssetBundle
{
	public
		$css = [

		],
		$js = [
			'js/exchange_currency.js'
		],
		$depends = [
			'yii\web\JqueryAsset',
			'yii\web\YiiAsset',
			'yii\bootstrap\BootstrapAsset',
		],
		$publishOptions = [
			'forceCopy' => true
		];

	public function init()
	{
		$this->sourcePath = __DIR__;
		parent::init();
	}

}