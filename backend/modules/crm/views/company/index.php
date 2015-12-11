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

$this->title = Yii::t('app/crm','CRM company');

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
					'columns' => [
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
								return is_object($manager) ? $manager->username : NULL;
							},
							'filter' => \backend\models\BUser::getListManagers()
						],
					],
				]); ?>

			</div>
		</div>
	</div>
</div>
