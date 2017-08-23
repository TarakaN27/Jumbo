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
        [
            'label' => Yii::t('app/common','Rate name'),
            'attribute' => 'name',
        ],
        [
            'label' => Yii::t('app/common','Rate code'),
            'attribute' => 'code',
        ],
		[
			'label' => Yii::t('app/common','Rate BYN'),
			'value' => function($model){
				return Yii::$app->formatter->asDecimal($model['rate_nbrb'], 4);
			}
		],
		[
			'attribute' => 'rate_nbrb',
			'label' => Yii::t('app/common','Rate BYR'),
			'value' => function($model){
				return Yii::$app->formatter->asDecimal(round((float)$model['rate_nbrb']*10000),0);
			}
		],
	]
])?>
<div>
	<?=Yii::t('app/common','Update time');?>: <?=is_null($maxUpdate) ? '' : Yii::$app->formatter->asDatetime($maxUpdate);?>
</div>
