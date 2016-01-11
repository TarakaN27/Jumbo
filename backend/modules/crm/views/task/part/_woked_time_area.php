<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 6.1.16
 * Time: 9.53
 */
use yii\bootstrap\Modal;
use yii\helpers\Html;
$this->registerJs("

$('.table-time-log-area').on('click','.activity-update-link',
function() {
    $.get(
        '".\yii\helpers\Url::to(['update-log-time'])."',
        {
            id: $(this).closest('tr').data('key')
        },
        function (data) {
            $('.modal-body').html(data);
            $('#activity-modal').modal();
        }
    );
});
",\yii\web\View::POS_READY);?>


<?=\yii\grid\GridView::widget([
	'tableOptions' => [
		'class' => 'table table-striped no-margin table-time-log-area'
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
		[
			'class' => 'yii\grid\ActionColumn',
			'template' => '{update}',
			//'headerOptions' => ['width' => '20%', 'class' => 'activity-view-link',],
			//'contentOptions' => ['class' => 'padding-left-5px'],
			'buttons' => [
				'update' => function ($url, $model, $key) {
					return Html::a('<span class="glyphicon glyphicon-eye-open"></span>','#', [
						'class' => 'activity-update-link',
						'title' => Yii::t('yii', 'View'),
						'data-toggle' => 'modal',
						'data-target' => '#activity-modal',
						'data-id' => $key,
						'data-pjax' => '0',
					]);
				},
			],
		],
		/*
		[
			'label' => '',
			'value' => function($model){
				if($model->buser_id !== Yii::$app->user->id)
					return '';




			}
		],
		*/
	],
]);?>
