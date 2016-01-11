<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 11.1.16
 * Time: 11.33
 */
use yii\bootstrap\ActiveForm;
use kartik\date\DatePicker;
use yii\helpers\Html;
$this->registerCss("
div#log_date_ajax-kvdate{
margin-top:25px;
}
");

?>
<div>
	<?$form = ActiveForm::begin([
		'action' => \yii\helpers\Url::to(['update-log-time','id' => $model->id]),
		'id' => 'EditLogWorkID'
	]);
	echo Html::activeHiddenInput($model,'task_id');

	?>
	<div class="col-md-6 col-sm-6 col-xs-12">
		<?=$form->field($model,'log_date')->widget(DatePicker::className(),[
			'options' => [
				'id' => 'log_date_ajax',
			],
			'type' => DatePicker::TYPE_COMPONENT_APPEND,
			'pluginOptions' => [
				'autoclose'=>true,
				'format' => 'yyyy-M-dd'
			],
		])?>
	</div>
	<div class="col-md-6 col-sm-6 col-xs-12">
		<div class="col-md-12 col-sm-12 col-xs-12">
			<label><?=$model->getAttributeLabel('spend_time')?></label>
		</div>
		<div class="col-md-6 col-sm-6 col-xs-12">
			<?=$form->field($model,'hour')->textInput()?>
		</div>
		<div class="col-md-6 col-sm-6 col-xs-12">
			<?=$form->field($model,'minutes')->textInput()?>
		</div>
	</div>
	<div class="col-md-12 col-sm-12 col-xs-12">
		<?=$form->field($model,'description')->textarea()?>
	</div>
	<div class="form-group col-md-12 col-sm-12 col-xs-12">
		<?= Html::submitButton(Yii::t('app/crm', 'Send'),['class' => 'btn btn-success']) ?>
	</div>

	<?php ActiveForm::end();?>
</div>
