<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 5.4.16
 * Time: 17.11
 */
?>
<?=\yii\grid\GridView::widget([
	'dataProvider' => new \yii\data\ArrayDataProvider([
		'allModels' => $arRates
	]),
	'columns' => [
		'name',
		'code',
		'nbrb_rate'
	]
])?>
<div>
	<?=Yii::t('app/common','Update time');?>: <?=is_null($maxUpdate) ? '' : Yii::$app->formatter->asDatetime($maxUpdate);?>
</div>
