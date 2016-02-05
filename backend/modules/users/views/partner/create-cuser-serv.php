<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 4.11.15
 * Time: 16.37
 */
$this->title = Yii::t('app/users', 'Create Partner link cuser services');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/users', 'Partners'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
	<div class="col-md-12 col-sm-12 col-xs-12">
		<div class="x_panel">
			<div class="x_title">
				<h2><?= Html::encode($this->title) ?></h2>
				<section class="pull-right">
					<?=  Html::a(Yii::t('app/users', 'To list'), ['index'], ['class' => 'btn btn-warning']) ?>
				</section>
				<div class="clearfix"></div>
			</div>
			<div class="x_content partner-create">
				<div class="partner-form">

					<?php $form = ActiveForm::begin([
						'options' => [
							'class' => 'form-horizontal form-label-left',
							//'enctype' => 'multipart/form-data'
						],
						'fieldConfig' => [
							'template' => '<div class="form-group">{label}<div class="col-md-6 col-sm-6 col-xs-12">{input}</div><ul class="parsley-errors-list" >{error}</ul></div>',
							'labelOptions' => ['class' => 'control-label col-md-3 col-sm-3 col-xs-12'],
						],
					]); ?>

					<?=$form->field($model,'cuser_id')->widget(\kartik\select2\Select2::className(),[
						'data' => \common\models\CUser::getContractorMap(),
						'options' => ['placeholder' => Yii::t('app/users','Choose cuser')],
						'pluginOptions' => [
							'allowClear' => true
						],
					])?>

					<?=$form->field($model,'service_id')->widget(\kartik\select2\Select2::className(),[
						'data' => \common\models\Services::getServicesMap(),
						'options' => ['placeholder' => Yii::t('app/users','Choose service')],
						'pluginOptions' => [
							'allowClear' => true
						],
					])?>


					<?= $form->field($model,'connect')->widget(\kartik\date\DatePicker::className(),[
						'type' => \kartik\date\DatePicker::TYPE_COMPONENT_PREPEND,
						'pluginOptions' => [
							'autoclose'=>true,
							'format' => 'yyyy-m-dd',
							'orientation' => 'top left'
						]
					])?>

					<div class="form-group">
						<div class = "col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
							<?= Html::submitButton($model->isNewRecord ? Yii::t('app/users', 'Create') : Yii::t('app/users', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
						</div>
					</div>

					<?php ActiveForm::end(); ?>

				</div>
			</div>
		</div>
	</div>
</div>

