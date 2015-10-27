<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 20.10.15
 * Time: 15.09
 */
$this->title = Yii::t('app/users', 'Create external account');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/users', 'Cusers'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
	<div class="col-md-12 col-sm-12 col-xs-12">
		<div class="x_panel">
			<div class="x_title">
				<h2>Контрагенты <small>настройки</small></h2>
				<section class="pull-right">
					<?= Html::a(Yii::t('app/users', 'To list'), ['/users/contractor/index'], ['class' => 'btn btn-warning']) ?>
				</section>
				<div class="clearfix"></div>
			</div>
			<div class="x_content">
				<?php $form = ActiveForm::begin([
					'options' => [
						'class' => 'form-horizontal form-label-left'
					],
					'fieldConfig' => [
						'template' => '<div class="form-group">{label}<div class="col-md-6 col-sm-6 col-xs-12">{input}</div><ul class="parsley-errors-list" >{error}</ul></div>',
						'labelOptions' => ['class' => 'control-label col-md-3 col-sm-3 col-xs-12'],
					],
				]); ?>
				<?php echo $form->field($model,'pp_max')->textInput(); ?>
				<?php echo $form->field($model,'pp_percent')->textInput(); ?>

				<div class="form-group">
					<div class = "col-md-offset-8 ">
						<?= Html::submitButton($model->isNewRecord ? Yii::t('app/users', 'Save') : Yii::t('app/users', 'Update btn'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
					</div>
				</div>

				<?php ActiveForm::end(); ?>

			</div>
		</div>
	</div>
</div>

