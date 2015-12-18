<?php
/**
 *
 * @var #VobModelContact|? $model
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
	<div class="row">
		<div class="col-md-6">
			<?php echo $form->field($model,'manager_id')->widget(\kartik\select2\Select2::className(),[
				'data' => \backend\models\BUser::getListManagers(),
			]); ?>
		</div>
		<div class="col-md-6">
			<div class="form-group text-right">
				<?= Html::submitButton(
					Yii::t('app/crm', 'Change'),
					['class' =>'btn btn-primary btnContact']
				) ?>
			</div>
		</div>
	</div>
<?php
ActiveForm::end();

