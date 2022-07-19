<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 13.1.16
 * Time: 16.06
 */

namespace common\components\notification\widget\assets;


use yii\web\AssetBundle;

class TNWAssets extends AssetBundle
{
	public
		$js = [
			'js/socket_io/socket.io.js',
			'js/notification.js',
		],
		$publishOptions = [
			'forceCopy' => true
		],
		$depends = [
			'yii\web\JqueryAsset',
			'yii\web\YiiAsset',
			'yii\bootstrap\BootstrapAsset',
		];

	public function init()
	{
		$this->sourcePath = __DIR__;
		parent::init();
	}

}