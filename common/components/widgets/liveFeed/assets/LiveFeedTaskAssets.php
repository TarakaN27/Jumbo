<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 18.12.15
 * Time: 14.32
 */

namespace common\components\widgets\liveFeed\assets;


use yii\web\AssetBundle;

class LiveFeedTaskAssets extends AssetBundle
{
	public
		$css = [
			'css/life_feed_company.css',
	],
		$js = [
			'js/live_feed_task.js'
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