<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 18.12.15
 * Time: 16.07
 */
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\web\JsExpression;
$form = ActiveForm::begin([
	'options' => [
		'class' => 'text-left',
		'enctype' => 'multipart/form-data'
	],
]);
?>
	<div class="row">
		<div class="col-md-6">
			<?php echo $form->field($model,'buser_id')->widget(\kartik\select2\Select2::className(),[
				//'initValueText' => $sAssName, // set the initial display text
				'options' => [
					'placeholder' => Yii::t('app/crm','Search for a users ...')
				],
				'pluginOptions' => [
					'allowClear' => true,
					'minimumInputLength' => 2,
					'ajax' => [
						'url' => \yii\helpers\Url::to(['/ajax-select/get-b-user']),
						'dataType' => 'json',
						'data' => new JsExpression('function(params) { return {q:params.term}; }')
					],
					'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
					'templateResult' => new JsExpression('function(cmp_id) { return cmp_id.text; }'),
					'templateSelection' => new JsExpression('function (cmp_id) { return cmp_id.text; }'),
				],
			]); ?>
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