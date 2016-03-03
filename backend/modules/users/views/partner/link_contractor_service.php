<?php
use yii\helpers\Html;
use yii\grid\GridView;
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 4.11.15
 * Time: 15.48
 */
$this->title = Yii::t('app/users', 'Partners');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class = "row">
	<div class = "col-md-12 col-sm-12 col-xs-12">
		<div class = "x_panel">
			<div class = "x_title">
				<h2><?= Html::encode($this->title) ?></h2>
				<section class="pull-right">
					<?= Html::a(Yii::t('app/users', 'Create link'), ['create-cuser-serv','id' => $id], ['class' => 'btn btn-success']) ?>
				</section>
				<div class = "clearfix"></div>
			</div>
			<div class = "x_content">
				<?= GridView::widget([
					'dataProvider' => $dataProvider,
					'filterModel' => $searchModel,
					'columns' => [
						['class' => 'yii\grid\SerialColumn'],
						[
							'attribute' => 'cuser_id',
							'value' => function($model){
								return is_object($obCUser = $model->cuser) ? $obCUser->getInfo() : $model->cuser_id;
							},
							'filter' => \common\models\CUser::getContractorMap()
						],
						[
							'attribute' => 'service_id',
							'value' => function($model){
								return is_object($obServ = $model->service) ? $obServ->name : $model->service_id;
							},
							'filter' => \common\models\Services::getServicesMap()
						],
						'connect:date',
						[
							'class' => 'yii\grid\ActionColumn',
							'template' => '{delete}',
							'buttons' => [
								'delete' => function ($url, $model, $key) {
									$options =[
										'title' => Yii::t('yii', 'Delete'),
										'aria-label' => Yii::t('yii', 'Delete'),
										'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
										'data-method' => 'post',
										'data-pjax' => '0',
									];
									$url = ['delete-link-cuser-serv','id' => $model->id];
									return Html::a('<span class="glyphicon glyphicon-trash"></span>', $url, $options);
								}
							]
						],
					],
				]); ?>
			</div>
		</div>
	</div>
</div>

