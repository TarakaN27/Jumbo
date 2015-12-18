<?php
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
			<?php echo $form->field($model,'buser_id')->dropDownList(
				\backend\models\BUser::getAllMembersMap()
			); ?>
		</div>
		<div class="col-md-6">
			<div class="form-group text-right">
				<?= Html::submitButton(
					Yii::t('app/crm', 'add'),
					['class' =>'btn btn-primary btnContact']
				) ?>
			</div>
		</div>
	</div>
<?php
ActiveForm::end();