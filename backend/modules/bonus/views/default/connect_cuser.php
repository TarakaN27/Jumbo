<?php
/**
 *
 * @var \common\models\BonusScheme $model
 * @var \backend\modules\bonus\form\ConnectBonusToCuserForm $obForm
 * @var PsiWhiteSpace $data
 */
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\web\JsExpression;
$this->title = Yii::t('app/bonus','Connect user to bonus scheme')
?>
<div class="row">
	<div class="col-md-12 col-sm-12 col-xs-12">
		<div class="x_panel">
			<div class="x_title">
				<h2><?= Html::encode($this->title) ?></h2>
				<section class="pull-right">
					<?=  Html::a(Yii::t('app/bonus', 'To list'), ['index'], ['class' => 'btn btn-warning']) ?>
				</section>
				<div class="clearfix"></div>
			</div>
			<div class="x_content bonus-scheme-create">
				<?php $form = ActiveForm::begin([
					'options' => [
						'class' => 'form-horizontal form-label-left',
						'enctype' => 'multipart/form-data'
					],
					'fieldConfig' => [
						'template' => '<div class="form-group">{label}<div class="col-md-6 col-sm-6 col-xs-12">{input}</div><ul class="parsley-errors-list" >{error}</ul></div>',
						'labelOptions' => ['class' => 'control-label col-md-3 col-sm-3 col-xs-12'],
					],
				]);?>
				<?php echo $form
					->field($obForm,'users')
					->widget(\kartik\select2\Select2::className(),[
						'initValueText' => '', // set the initial display text
						'data' => $data,
						'options' => [
							'placeholder' => Yii::t('app/crm','Search for a users ...'),
							'multiple' => true
						],
						'pluginOptions' => [
							'allowClear' => true,
							'minimumInputLength' => 2,
							'ajax' => [
								'url' => \yii\helpers\Url::to(['/ajax-select/get-cmp']),
								'dataType' => 'json',
								'data' => new JsExpression('function(params) { return {q:params.term}; }')
							],
							'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
							'templateResult' => new JsExpression('function(cmp_id) { return cmp_id.text; }'),
							'templateSelection' => new JsExpression('function (cmp_id) { return cmp_id.text; }'),
						],
					]);?>
				<div class="form-group">
					<div class = "col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
						<?= Html::submitButton(Yii::t('app/bonus', 'Save') , ['class' =>'btn btn-success']) ?>
					</div>
				</div>
				<?php ActiveForm::end();?>
			</div>
		</div>
	</div>
</div>