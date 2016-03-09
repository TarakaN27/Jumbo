<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 6.1.16
 * Time: 9.53
 */
use yii\bootstrap\Modal;
use yii\helpers\Html;
$disableHidden = isset($disableHidden) ? $disableHidden : FALSE;
$visibleTaskID = isset($showTaskID) ? $showTaskID : FALSE;
$obModel = new \common\models\CrmTaskLogTime();
?>
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
			'attribute' => 'task_id',
			'label' => $obModel->getAttributeLabel('task_id'),
			'format' => 'raw',
			'visible' =>$visibleTaskID,
			'value' => function($model){
				return Html::a($model->task_id,['/crm/task/view','id' => $model->task_id],['target' => '_blank']);
			}
		],
		[
			'attribute' => 'buser_id',
			'label' => $obModel->getAttributeLabel('buser_id'),
			'value' => function($model){
				return ($obUser = $model->buser) ? $obUser->getFio() : $model->buser_id;
			}
		],
		[
			'attribute' => 'spend_time',
			'label' => $obModel->getAttributeLabel('spend_time'),
			'value' => function($model){
				return $model->getFormatedSpendTime();
			}
		],
		[
			'attribute' => 'description',
			'format' => 'text',
			'label' => $obModel->getAttributeLabel('description'),
		],
		[
			'attribute' => 'created_at',
			'format' => 'datetime',
			'label' => $obModel->getAttributeLabel('created_at'),
		],
		[
			'class' => 'yii\grid\ActionColumn',
			'template' => '{update}',
			//'headerOptions' => ['width' => '20%', 'class' => 'activity-view-link',],
			//'contentOptions' => ['class' => 'padding-left-5px'],
			'buttons' => [
				'update' => function ($url, $model, $key) use ($disableHidden) {
					if($model->buser_id !== Yii::$app->user->id)
						return '';

					return Html::a('<span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>','#', [
						'class' => 'activity-update-link '.($disableHidden ? '' : 'hidden') ,
						'title' => Yii::t('yii', 'Update'),
						'data-toggle' => 'modal',
						'data-target' => '#activity-modal',
						'data-id' => $model->id,
						'data-pjax' => '0',
					]);
				},
			],
		],
	],
]);?>
