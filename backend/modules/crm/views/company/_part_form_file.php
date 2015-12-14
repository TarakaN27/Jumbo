<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 11.12.15
 * Time: 18.09
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
			<?php echo $form->field($model,'name')->textInput(); ?>
			<?php echo $form->field($model,'src')->fileInput(); ?>
		</div>
	</div>
	<div class="row">

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
