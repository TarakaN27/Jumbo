<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 11.2.16
 * Time: 12.01
 */
use yii\helpers\Html;
$this->title = Yii::t('app/reports','Timesheet');
$this->registerCssFile('@web/css/daterangepicker/daterangepicker.css');
$this->registerJsFile('@web/js/moments/moment-with-locales.js',[
	'depends' => [
		'yii\web\YiiAsset',
		'yii\bootstrap\BootstrapAsset'
	],
]);
$this->registerJsFile('@web/js/datepicker/daterangepicker.js',[
	'depends' => [
		'yii\web\YiiAsset',
		'yii\bootstrap\BootstrapAsset',
	],
]);
$this->registerJsFile('@web/js/wm_app/timesheet.js',[
	'depends' => [
		'yii\web\YiiAsset',
		'yii\bootstrap\BootstrapAsset',
	],
]);
$this->registerJs("
	var
		TIMESHEET_TITLE = '".Yii::t('app/reports','Timesheet')."',
		TIMESHEET_ERROR_LOAD = '".Yii::t('app/reports','TIMESHEET_ERROR_LOAD')."',
		USER_ID = ".Yii::$app->user->id.",
		URL_GET_TIMESHEET = '".\yii\helpers\Url::to(['get-time-sheet'])."';
",\yii\web\View::POS_HEAD);
?>
<div class="row">
	<div class="col-md-12 col-sm-12 col-xs-12">
		<div class="x_panel">
			<div class="x_title">
				<h2><?= Html::encode($this->title) ?></h2>
				<section class="pull-right">
					<div id="reportrange" class="pull-right" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; width: 100%">
						<i class="glyphicon glyphicon-calendar fa fa-calendar"></i>&nbsp;
						<span></span> <b class="caret"></b>
					</div>
				</section>
				<div class="clearfix"></div>
			</div>
			<div class="x_content horizontal-scroll" id="main-contener">
				<div class="loader mrg-auto"></div>

			</div>
		</div>
	</div>
</div>