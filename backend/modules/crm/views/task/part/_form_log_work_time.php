<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 5.1.16
 * Time: 15.14
 */
use yii\bootstrap\ActiveForm;
use kartik\date\DatePicker;
use yii\helpers\Html;
$this->registerJs("
$('body').on('beforeSubmit', 'form#logWorkID', function () {
     var form = $(this);
     // return false if form still have some validation errors
     if (form.find('.has-error').length) {
          return false;
     }
     // submit form
     $.ajax({
          url: form.attr('action'),
          type: 'post',
          data: form.serialize(),
          success: function (response) {
               // do something with response
          }
     });
     return false;
});
");
$this->registerJsFile('@web/js/duration.js', [
	'depends' => [
		\yii\web\JqueryAsset::className(),
		\yii\jui\JuiAsset::className()

	]]);
$this->registerJs("
durationPicker.init('crmtasklogtime-spend_time', 3600);
",\yii\web\View::POS_READY);
?>
<div>
<?$form = ActiveForm::begin([
	'action' => \yii\helpers\Url::to(['send-log-work']),
	'id' => 'logWorkID'
]);?>
	<div class="col-md-6 col-sm-6 col-xs-12">
		<?=$form->field($model,'log_date')->widget(DatePicker::className(),[
			'type' => DatePicker::TYPE_COMPONENT_APPEND,
			'pluginOptions' => [
				'autoclose'=>true,
				'format' => 'yyyy-M-dd'
			],
		])?>
	</div>
	<div class="col-md-6 col-sm-6 col-xs-12">
		<?=$form->field($model,'spend_time')->textInput();?>
	</div>
	<div class="col-md-12 col-sm-12 col-xs-12">
		<?=$form->field($model,'description')->textarea()?>
	</div>
	<div class="form-group col-md-12 col-sm-12 col-xs-12">

			<?= Html::submitButton($model->isNewRecord ? Yii::t('app/documents', 'Send') : Yii::t('app/documents', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>

	</div>

<?php ActiveForm::end();?>
</div>
