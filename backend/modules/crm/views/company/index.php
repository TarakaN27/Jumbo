<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 9.12.15
 * Time: 16.35
 */

use yii\helpers\Html;
use yii\grid\GridView;
use common\components\helpers\CustomHelper;
use common\models\CUser;
use common\models\BUserCrmRules;

$this->title = Yii::t('app/crm','CRM company');
$columns = [
	['class' => 'yii\grid\SerialColumn'],
	[
		'attribute' => 'corp_name',
		'label' => Yii::t('app/users', 'Corp Name'),
		'format' => 'html',
		'value' => function($model){
			/** @var CUserRequisites $obR */
			$obR = $model->requisites;
			if(empty($obR))
				return Html::a('N/A',['view','id' => $model->id],['class'=>'link-upd']);
			return Html::a(
				CustomHelper::highlight('dummy',$obR->getCorpName()),
				['view','id' => $model->id],
				['class'=>'link-upd']);
		}
	],
	[
		'attribute' => 'fio',
		'label' => Yii::t('app/users','FIO'),
		'format' => 'html',
		'value' => function($model){
			/** @var CUserRequisites $obR */
			$obR = $model->requisites;
			if(empty($obR))
				return 'N/A';
			return CustomHelper::highlight('dummy',$obR->j_lname.' '.$obR->j_fname.' '.$obR->j_mname);
		}
	],
	[
		'attribute' => 'phone',
		'label' => Yii::t('app/users','Phone'),
		'format' => 'html',
		'value' => function($model)
		{
			/** @var CUserRequisites $obR */
			$obR = $model->requisites;
			if(empty($obR))
				return 'N/A';
			return CustomHelper::highlight('dummy',$obR->c_phone);
		}
	],
	[
		'attribute' => 'c_email',
		'label' => Yii::t('app/users','Email'),
		'format' => 'html',
		'value' => function($model)
		{
			/** @var CUserRequisites $obR */
			$obR = $model->requisites;
			if(empty($obR))
				return 'N/A';
			return CustomHelper::highlight('dummy',$obR->c_email);
		}
	],
	[
		'attribute' => 'manager_id',
		'value' => function($model){
			$manager = $model->manager;
			return is_object($manager) ? $manager->getFio() : NULL;
		},
		'filter' => \backend\models\BUser::getListManagers()
	],
	[
		'attribute' => 'contractor',
		'value' => function($model){
			return $model->getContractorStr();
		},
		'filter' => CUser::getContractorArr()
	]
];
$additionBlock = [];
if(Yii::$app->user->can('adminRights')) {
	array_push($columns,
		[
			'label' => '',
			'format' => 'raw',
			'value' => function ($model) {
				return '
               <div class="btn-group">
                   <button data-toggle="dropdown" class="btn btn-default dropdown-toggle" type="button" aria-expanded="false">
                       ' . Yii::t('app/users', 'Settings') . ' <span class="caret"></span>
                   </button>
                   <ul class="dropdown-menu">
                       <li>
                           ' . Html::a(Yii::t('app/users', 'Settings'), ['/users/contractor-settings/index', 'userID' => $model->id]) . '
                       </li>
                       <li>
                           ' . Html::a(Yii::t('app/users', 'Prefer condition'), ['/users/contractor/prefer-cond', 'id' => $model->id]) . '
                       </li>
                       <li>
                           ' . Html::a(Yii::t('app/users', 'External account'), ['/users/contractor/external-account', 'iCID' => $model->id]) . '
                       </li>
                       <li>
                           ' . Html::a(Yii::t('app/users', 'Services contract'), ['/users/contractor/services-contract', 'iCID' => $model->id]) . '
                       </li>
                   </ul>
               </div>
        ';
			}
		]
	);
}

array_push($columns,[
	'class' => 'yii\grid\ActionColumn',
	'template' => '{update}',
	'buttons' => [
		'update' => function($url, $model, $key){
			$show = Yii::$app->user->crmCanEditModel(
				$model,
				'created_by',
				'manager_id',
				'opened'
			);
			$options = [
				'title' => Yii::t('yii', 'Update'),
				'aria-label' => Yii::t('yii', 'Update'),
				'data-pjax' => '0',
			];
			return $show ? Html::a('<span class="glyphicon glyphicon-pencil"></span>', $url, $options) : NULL;
		},
	]
]);
array_push($columns,[
	'class' => 'yii\grid\ActionColumn',
	'template' => '{archive}',
	'buttons' => [
		'archive' => function($url, $model, $key){
			$show = Yii::$app->user->crmCanDeleteModel(
				$model,
				'created_by',
				'manager_id',
				'opened'
			);

			$color = $model->archive == CUser::ARCHIVE_YES ? 'red' :'';
			$options = [
				'title' => Yii::t('app/crm', 'Archive'),
				'aria-label' => Yii::t('app/crm', 'Archive'),
				'data-pjax' => '0',
			];
			return $show ? Html::a('<i class="fa fa-archive '.$color.'"></i>', $url, $options) : NULL;
		},
	]
]);

if(Yii::$app->user->can('adminRights')) {
	array_push($columns,[
		'class' => 'yii\grid\ActionColumn',
		'template' => '{delete}',
	]);
}

?>
<div class = "row">
	<div class = "col-md-12 col-sm-12 col-xs-12">
		<div class = "x_panel">
			<div class = "x_title">
				<h2><?php echo Html::encode($this->title)?></h2>
				<section class="pull-right">
					<?php echo \yii\helpers\Html::a(Yii::t('app/crm','Add_new_company'),['create'],['class'=>'btn btn-primary']);?>
				</section>
				<div class = "clearfix"></div>
			</div>
			<div class = "x_content">

				<?php echo \common\components\widgets\WMCPageSize\WMCPageSize::widget();?>
				<?= GridView::widget([
					'dataProvider' => $dataProvider,
					'filterModel' => $searchModel,
					'filterSelector' => 'select[name="per-page"]',
					'columns' => $columns
				]); ?>

			</div>
		</div>
	</div>
</div>
