<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 14.12.15
 * Time: 14.26
 */

namespace common\components\widgets\liveFeed\assets;


use yii\web\AssetBundle;

class LifeFeedCompanyAssets extends AssetBundle
{
	public
		$css = [
			'css/life_feed_company.css',
		],
		$js = [
			'js/life_feed_company.js'
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