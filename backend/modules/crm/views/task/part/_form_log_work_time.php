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
          success: function (res) {
               if(res && res.content != '')
               {
                    $('#tab_content2').html(res.content);
					addSuccessNotify(TASK,'".Yii::t('app/crm','Time successfully spent')."');
					$('.company-time-control .modal-dialog button.close').click();
					$('.user-time').html(res.timeSpend);
               }else{
					addErrorNotify(TASK,'".Yii::t('app/crm','Error. Can not log time')."');
               }
          }
     });
     return false;
});
");
$this->registerCss("
div#crmtasklogtime-log_date-kvdate{
margin-top:25px;
}
");
?>
<div>
<?$form = ActiveForm::begin([
	'action' => \yii\helpers\Url::to(['send-log-work']),
	'id' => 'logWorkID'
]);
echo Html::activeHiddenInput($model,'task_id');

?>
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
