<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 18.12.15
 * Time: 14.12
 */
use yii\helpers\Html;
use vova07\imperavi\Widget as ImperaviWidget;
?>
<div class="message_wrapper ">
	<ul class="list-unstyled msg_list" data-id="<?=$obDialog->id;?>">
		<?php echo $this->render('_dialogs_crm_comment',[
			'models' => $arMessages,
			'pag' => $pag,
			'dID'=>$obDialog->id,
			'disableClick' => TRUE
		])?>
	</ul>
</div>
<div class="message_wrapper form-add-msg <?=$uniqStr?>" data-id="<?=$obDialog->id;?>">
	<form onsubmit = "return false;" class = "msgBox" data-id = "<?=$obDialog->id;?>">
		<?php echo Html::hiddenInput('dialog_id', $obDialog->id); ?>
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
				<button class = "btn btn-success btn-sm addCmpMsg" data = "<?=$obDialog->id;?>" type = "button">
					<?= Yii::t('app/common', 'Add message') ?>
				</button>
			</div>
		</div>
	</form>
</div>
