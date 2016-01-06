<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 6.1.16
 * Time: 9.53
 */
?>
<?=\yii\grid\GridView::widget([
	'tableOptions' => [
		'class' => 'table table-striped no-margin'
	],
	'dataProvider' => (New \yii\data\ArrayDataProvider([
		'allModels' => $obLog
	])),
	'columns' => [
		['class' => 'yii\grid\SerialColumn'],
		[
			'attribute' => 'buser_id',
			'value' => function($model){
				return ($obUser = $model->buser) ? $obUser->getFio() : $model->buser_id;
			}
		],
		[
			'attribute' => 'spend_time',
			'value' => function($model){
				return $model->getFormatedSpendTime();
			}
		],
		'description:text',
		'created_at:datetime',
	],
]);?>
