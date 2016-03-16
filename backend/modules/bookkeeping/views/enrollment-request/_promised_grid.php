<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 16.3.16
 * Time: 13.16
 */
?>
<?=\yii\grid\GridView::widget([
	'dataProvider' => new \yii\data\ArrayDataProvider([
		'allModels' => $arPromised,
	]),
	'columns' => [
		[
			'attribute' => 'amount',
			'label' => Yii::t('app/book','Unit amount'),
			'value' => function($model){
				$repay = $model->repay;
				$repAmount = 0;
				foreach($repay as $rep)
					$repAmount+=$rep->amount;
				return $model->amount-$repAmount.'('.$model->amount.'/'.$repAmount.')';
			}
		],
		[
			'attribute' => 'description',
			'label' => Yii::t('app/book','Description')
		],
		[
			'attribute' => 'owner',
			'label' => Yii::t('app/book','Owner'),
			'value' => function($model){
				return is_object($obBuser = $model->addedBy) ? $obBuser->getFio() : NULL;
			}
		],
		[
			'attribute' => 'service_id',
			'label' => Yii::t('app/book','Service'),
			'value' => function($model){
				return is_object($obServ = $model->service) ? $obServ->name : NULL;
			}
		]
	]

])?>
