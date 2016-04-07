<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 6.4.16
 * Time: 13.30
 */
use yii\widgets\ActiveForm;
?>
<?php $form = ActiveForm::begin([
	'options' => [
		'class' => 'upd-msg',
		'enctype' => 'multipart/form-data',
		'action' => NULL
	],
	'fieldConfig' => [
		//'template' => '<div class="form-group">{label}<div class="col-md-6 col-sm-6 col-xs-12">{input}</div><ul class="parsley-errors-list" >{error}</ul></div>',
		//'labelOptions' => ['class' => 'control-label col-md-3 col-sm-3 col-xs-12'],
	],
]); ?>
	<?=$form->field($model,'msg')->textarea([
		'class' => 'upd-textarea'
	])?>
<?php ActiveForm::end(); ?>
