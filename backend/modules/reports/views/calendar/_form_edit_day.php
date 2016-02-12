<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 9.2.16
 * Time: 15.40
 */
use yii\helpers\Html;
$wtSett = [];
if($model->type == \common\models\CalendarDays::TYPE_HOLIDAY)
{
	$wtSett = ['disabled' => 'disabled'];
}
?>
<?php $form= \yii\bootstrap\ActiveForm::begin([
	'id' => 'editdateform',
	'action' => \yii\helpers\Url::to(['save']),
]);
echo Html::hiddenInput('year',$year);
echo Html::activeHiddenInput($model,'id');
echo Html::activeHiddenInput($model,'date');
?>
<div class="row">
	<div class = "col-md-6 col-sm-6 col-xs-12">
		<?php echo $form->field($model,'type')->radioList(\common\models\CalendarDays::getTypeArr());?>
	</div>
	<div class = "col-md-6 col-sm-6 col-xs-12">
		<?php echo $form->field($model,'work_hour')->textInput($wtSett);?>
	</div>
</div>

<?php echo $form->field($model,'description')->textarea();?>

<div class="form-group">
	<div class = "col-md-6 col-sm-6 col-xs-12">
		<?= Html::submitButton(Yii::t('app/crm', 'Update'), ['class' => 'btn btn-success']) ?>
	</div>
</div>


<?php \yii\bootstrap\ActiveForm::end();?>
