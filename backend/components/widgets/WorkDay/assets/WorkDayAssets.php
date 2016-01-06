<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 6.1.16
 * Time: 10.57
 */
namespace backend\components\widgets\WorkDay\assets;

use yii\web\AssetBundle;
class WorkDayAssets extends AssetBundle
{
	public
		$css = [
			'css/work_day_widget.css',
		],
		$js = [
			'js/work_day_widget.js'
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