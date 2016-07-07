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
		[
			'label' => Yii::t('app/common','Rate BYN'),
			'value' => function($model){
				return $model->nbrb_rate;
			}
		],
		[
			'attribute' => 'nbrb_rate',
			'label' => Yii::t('app/common','Rate BYR'),
			'value' => function($model){
				return round((float)$model->nbrb_rate*10000);
			}
		],
	]
])?>
<div>
	<?=Yii::t('app/common','Update time');?>: <?=is_null($maxUpdate) ? '' : Yii::$app->formatter->asDatetime($maxUpdate);?>
</div>
