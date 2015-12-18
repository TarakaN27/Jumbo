<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 16.12.15
 * Time: 14.42
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
			<?php echo $form->field($model,'assigned_id')->dropDownList(
				\backend\models\BUser::getAllMembersMap()
			); ?>
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