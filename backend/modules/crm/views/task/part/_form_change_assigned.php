<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 16.12.15
 * Time: 14.42
 */
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
$this->registerCssFile('@web/css/select/select2.min.css');
$this->registerJsFile('@web/js/select/select2.full.js',['depends' => [\yii\web\JqueryAsset::className()]]);

$this->registerJs("
$('#assigned_right_sidebar').select2({
	data : ".\yii\helpers\Json::encode(\backend\models\BUser::getAllMembersMap()).",
	theme : 'krajee'
});


",\yii\web\View::POS_READY);

$form = ActiveForm::begin([
	'id' => 'task_assigned_form',
	'action' => \yii\helpers\Url::to(['/crm/task/change-assigned','id' => $model->id]),
	'options' => [
		'class' => 'text-left',
		'enctype' => 'multipart/form-data',
		'onsubmit' => 'return false;'
	]
]);
?>
	<div class="row">
		<div class="col-md-6">
			<?php echo $form->field($model,'assigned_id')->dropDownList(\backend\models\BUser::getAllMembersMap(),[
				'id' => 'assigned_right_sidebar',
				'class' => 'controll'
			])
			?>
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