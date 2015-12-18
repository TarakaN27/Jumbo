<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 16.12.15
 * Time: 15.25
 */

namespace common\components\widgets\liveFeed\assets;


use yii\web\AssetBundle;

class LifeFeedContactAssets extends AssetBundle
{
	public
		$css = [
		'css/life_feed_company.css',
	],
		$js = [
		'js/life_feed_contact.js'
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