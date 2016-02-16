<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 16.2.16
 * Time: 12.00
 */
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
$this->title = Yii::t('app/users','Норма часы');
?>
<div class = "row">
	<div class = "col-md-12 col-sm-12 col-xs-12">
		<div class = "x_panel">
			<div class = "x_title">
				<h2><?php echo Html::encode($this->title);?></h2>
				<section class="pull-right">
					<?= Html::a(Yii::t('app/users', 'To list'), ['/crm/company/index'], ['class' => 'btn btn-warning']) ?>
				</section>
				<div class = "clearfix"></div>
			</div>
			<div class = "x_content">
				<div class = "col-md-4 col-sm-4 col-xs-12 ">

				<?php $form = ActiveForm::begin();?>

					<?php echo $form->field($obQHour,'hours')->textInput([]);?>

					<div class = "form-group">
							<?=Html::submitButton($obQHour->isNewRecord ? Yii::t('app/users', 'Create') : Yii::t('app/users', 'Update btn'),
								['class' => $obQHour->isNewRecord ? 'btn btn-success' : 'btn btn-primary'])?>
					</div>
				<?php ActiveForm::end();?>
				</div>
			</div>
		</div>
	</div>
</div>

