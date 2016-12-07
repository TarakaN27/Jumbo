<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 16.3.16
 * Time: 13.16
 */
use yii\web\View;

foreach($arPromised as $key => $promise){
    $promise->sort = $key+1;
}
?>


<?=\yii\grid\GridView::widget([
	'dataProvider' => new \yii\data\ArrayDataProvider([
		'allModels' => $arPromised,

	]),
	'rowOptions'=>[],
	'tableOptions'=>['id'=>'resortingTable', 'class'=>'display table table-striped table-bordered'],
	'columns' => [
		[
			'attribute' => 'active',
			'contentOptions' => ['width'=>'5%'],
			'label' => false,
			'format'=>'raw',
			'value'=>function($model){
				return $model->sort;
		}
		],
		[
			'attribute' => 'active',
			'label' => false,
			'format'=>'raw',
			'value' => function($model){
				$repay = $model->repay;
				$repAmount = 0;
				foreach($repay as $rep)
					$repAmount+=$rep->amount;
				return '<input type="checkbox" name="EnrollProcessForm[promise-active][]" checked="" value="'.$model->id.'" data-amount="'.($model->amount-$repAmount).'" />
						<input type="hidden" name="EnrollProcessForm[promise-sort][]" value="'.$model->id.'" />';
			}
		],
		[
			'attribute' => 'amount',
			'label' => Yii::t('app/book','Unit amount'),
			'value' => function($model){
				$repay = $model->repay;
				$repAmount = 0;
				foreach($repay as $rep)
					$repAmount+=$rep->amount;
				return Yii::$app->formatter->asDecimal($model->amount-$repAmount).'('.Yii::$app->formatter->asDecimal($model->amount).'/'.Yii::$app->formatter->asDecimal($repAmount).')';
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
			'label' => Yii::t('app/book','Service'),
			'value' => function($model){
				return is_object($obServ = $model->service) ? $obServ->name : NULL;
			}
		],
		[
			'attribute' => 'cuser_id',
			'label' => Yii::t('app/book','Cuser ID'),
			'value' => function($model){
				return is_object($obCuser = $model->cuser) ? $obCuser->getInfoWithSite() : NULL;
			}
		]
	]

])?>
