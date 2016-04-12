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
use yii\web\JsExpression;

$this->title = Yii::t('app/crm','CRM company');
$columns = [
	['class' => 'yii\grid\SerialColumn'],
	[
		'attribute' => 'corp_name',
		'label' => Yii::t('app/users', 'Corp Name'),
		'format' => 'html',
		'value' => function($model) use ($arCompanyRedisList){
			/** @var CUserRequisites $obR */
			$obR = $model->requisites;

			$addStr =  in_array($model->id,$arCompanyRedisList) ? '<span class="label label-primary">New</span>' : '';

			$corpName = empty($obR) ? 'N/A ' : CustomHelper::highlight('dummy',$obR->getCorpName());

			if(Yii::$app->user->can('only_jurist') || Yii::$app->user->can('only_e_marketer'))
			{
				return $corpName;
			}else{
				return Html::a($corpName,['view','id' => $model->id],['class'=>'link-upd']).' '.$addStr;
			}
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
		'attribute' => 'prospects_id',
		'value' => function($model){
			return is_object($obPr = $model->prospects) ? $obPr->name : '';
		},
		'filter' => \common\models\CuserProspects::getProspectsTypeMap()
	],
	[
		'attribute' => 'source_id',
		'filter' => \common\models\CuserSource::getSourceMap(),
		'value' => function($model){
			return is_object($obSource = $model->source) ? $obSource->name : NULL;
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
		'attribute' => 'manager.fio',
		'filter' => \kartik\select2\Select2::widget([
			'model' => $searchModel,
			'attribute' => 'manager_id',
			'initValueText' => $manValue, // set the initial display text
			'options' => [
				'placeholder' => Yii::t('app/crm','Search for a users ...')
			],
			'pluginOptions' => [
				'allowClear' => true,
				'minimumInputLength' => 2,
				'ajax' => [
					'url' => \yii\helpers\Url::to(['/ajax-select/get-b-user']),
					'dataType' => 'json',
					'data' => new JsExpression('function(params) { return {q:params.term}; }')
				],
				'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
				'templateResult' => new JsExpression('function(cmp_id) { return cmp_id.text; }'),
				'templateSelection' => new JsExpression('function (cmp_id) { return cmp_id.text; }'),
			],
		])
	],
	[
		'attribute' => 'managerCrc.fio',
		'label' => Yii::t('app/users','CRC manager'),
		'filter' => \kartik\select2\Select2::widget([
			'model' => $searchModel,
			'attribute' => 'manager_crc_id',
			'initValueText' => $manCrcValue, // set the initial display text
			'options' => [
				'placeholder' => Yii::t('app/crm','Search for a users ...')
			],
			'pluginOptions' => [
				'allowClear' => true,
				'minimumInputLength' => 2,
				'ajax' => [
					'url' => \yii\helpers\Url::to(['/ajax-select/get-b-user']),
					'dataType' => 'json',
					'data' => new JsExpression('function(params) { return {q:params.term}; }')
				],
				'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
				'templateResult' => new JsExpression('function(cmp_id) { return cmp_id.text; }'),
				'templateSelection' => new JsExpression('function (cmp_id) { return cmp_id.text; }'),
			],
		])
	],
	[
		'attribute' => 'contractor',
		'value' => function($model){
			return $model->getContractorStr();
		},
		'filter' => CUser::getContractorArr()
	],
	[
		'label' => Yii::t('app/users','Quantity hours'),
		'attribute' => 'quantityHour',
		'format' => 'raw',
		'value' => function($model){
			$obQHour = $model->quantityHour;
			if(!$obQHour)
				return NULL;

			$hours = empty($obQHour->hours) ? 0 : $obQHour->hours;
			$spent = empty($obQHour->spent_time) ? 0 : $obQHour->spent_time;
			$item = $hours-$spent;

			if($item < 0)
				$spanOpt = ['class' => 'ts_red'];
			else
				$spanOpt = ['class' => 'ts_green'];

			return Html::tag('span',$item,$spanOpt);
		},
		'filter' => false
	]
];

$additionBlock = [];
if(Yii::$app->user->can('adminRights') || Yii::$app->user->can('only_jurist') || Yii::$app->user->can('only_manager')) {
	array_push($columns,
		[
			'label' => '',
			'format' => 'raw',
			'value' => function ($model) {

				$str = '';
				if(Yii::$app->user->can('only_manager'))
				{
					if($model->manager_id == Yii::$app->user->id)
						$str = '<li>
	                           ' . Html::a(Yii::t('app/users', 'Services contract'), ['/users/quantity-hours/index', 'iCID' => $model->id]) . '
	                       </li>';
				}elseif(Yii::$app->user->can('only_jurist'))
				{
					$str = '<li>
                           ' . Html::a(Yii::t('app/users', 'Services contract'), ['/users/contractor/services-contract', 'iCID' => $model->id]) . '
                       </li>';
				}elseif(Yii::$app->user->can('adminRights')){
					$str = '<li>
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
                       <li>
	                           ' . Html::a(Yii::t('app/users', 'Quantity hours'), ['/users/quantity-hours/index', 'iCID' => $model->id]) . '
	                       </li>
                       ';
				}
				return '
               <div class="btn-group">
                   <a data-toggle="dropdown" class="link-btn-cursor dropdown-toggle" type="button" aria-expanded="false">
                       <i class="glyphicon glyphicon-cog"></i>
                   </a>
                   <ul class="dropdown-menu">
                       '.$str.'
                   </ul>
               </div>
        ';
			}
		]
	);
}

array_push($columns,[
	'label' => '',
	'format' => 'raw',
	'value' => function($model){
		return Html::a(
			'<i class="fa fa-eye"></i>',
			['view-requisites','id' => $model->id],
			['target' => '_blank']);
	}

]);

if(!Yii::$app->user->can('only_jurist'))
{
	array_push($columns,[
		'class' => 'yii\grid\ActionColumn',
		'template' => '{update}',
		'buttons' => [
			'update' => function($url, $model, $key){
				$show = Yii::$app->user->crmCanEditModel(
					$model,
					'created_by',
					'manager_id',
					'is_opened'
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
/*
	array_push($columns,[
		'class' => 'yii\grid\ActionColumn',
		'template' => '{archive}',
		'buttons' => [
			'archive' => function($url, $model, $key){
				$show = Yii::$app->user->crmCanDeleteModel(
					$model,
					'created_by',
					'manager_id',
					'is_opened'
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
*/
}

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
					<?php if(
						Yii::$app->user->can('adminRights') ||
						Yii::$app->user->can('only_bookkeeper') ||
						Yii::$app->user->can('only_manager') ||
						Yii::$app->user->can('only_jurist')
					):?>
						<?php echo \yii\helpers\Html::a(Yii::t('app/crm','Add_new_company'),['create'],['class'=>'btn btn-primary']);?>
					<?php endif;?>
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
