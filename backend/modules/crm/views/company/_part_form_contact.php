<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 11.12.15
 * Time: 16.05
 */
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
$form = ActiveForm::begin([
	'options' => [
		'class' => 'text-left',
		'enctype' => 'multipart/form-data'
	],
]);
?>
<div class="row ">
	<div class="col-md-6">
		<?php echo $form->field($model,'fio')->textInput(); ?>
	</div>
	<div class="col-md-6">
		<?php echo $form->field($model,'post')->textInput(); ?>
	</div>
</div>
<div class="row">
	<div class="col-md-6">
		<?php echo $form->field($model,'phone')->textInput(); ?>
	</div>
	<div class="col-md-6">
		<?php echo $form->field($model,'email')->textInput(); ?>
	</div>
</div>
<div class="row">
	<div class="col-md-6">
		<?php echo $form->field($model,'description')->textarea(); ?>
	</div>
	<div class="col-md-6">
		<?php echo $form->field($model,'addition_info')->textarea(); ?>
	</div>
</div>
	<div class="row">
		<div class="col-md-6">
			<?php echo $form->field($model,'assigned_at')->widget(\kartik\select2\Select2::className(),[
				'data' => \backend\models\BUser::getListManagers(),
			]); ?>
		</div>
		<div class="col-md-6">
			<div class="form-group text-right">
				<?= Html::submitButton(
					$model->isNewRecord ? Yii::t('app/documents', 'Create') : Yii::t('app/documents', 'Update'),
					['class' => $model->isNewRecord ? 'btn btn-success btnContact' : 'btn btn-primary btnContact']
				) ?>
			</div>
		</div>
	</div>



<?php
ActiveForm::end();