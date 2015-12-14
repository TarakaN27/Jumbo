<?php
use yii\helpers\Html;
use kartik\select2\Select2;
use \vova07\imperavi\Widget as ImperaviWidget;
?>
<div class="row dialog-control">
<section class="new_dialog_feed">
	<div class="wraperNewDialog">
		<?=Html::button(Yii::t('app/common','Add new dialog'),[
			'class' => 'btn btn-success btn-xs pull-right',
			'id' => 'newDialogBtn'
		])?>
	</div>
	<div class="formBlock">
		<form onsubmit = "return false;" class = "msgBox" data-id = "0">
			<?php echo Html::hiddenInput('dialog_id', 0); ?>
			<?php echo Html::hiddenInput('cmp_id',$iCmpID)?>
			<?php echo Html::hiddenInput('author_id', Yii::$app->user->id); ?>
			<div class = "x_content">
				<?php echo Html::label(Yii::t('app/common','Message'))?>
				<?php echo ImperaviWidget::widget([
					'name' => 'redactor',
					'settings' => [
						'lang' => 'ru',
						'minHeight' => 200,
						'plugins' => [
							'clips',
							'fullscreen'
						]
					]
				]);?>
				<br />
				<div class = "form-group">
					<button class = "btn btn-success btn-sm addDialog" data = "0" type = "button">
						<?= Yii::t('app/common', 'Add dialog') ?>
					</button>
				</div>
			</div>
		</form>
	</div>
</section>
</div>
<!-- start recent activity -->
<ul class="messages company-msg msgBoxList">
	<?php if($obDialogs && $obModels = $obDialogs->getModels()):?>
			<?= $this->render('_dialog_crm_msg', ['models' => $obModels,'pag' => $pagination]) ?>
	<?php else:?>
		<p class="emptyDialog"><?php echo Yii::t('app/crm','No dialogs at feed')?>
	<?php endif;?>
</ul>
<!-- end recent activity -->
