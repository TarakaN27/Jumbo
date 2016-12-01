<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 18.12.15
 * Time: 14.12
 */
use yii\helpers\Html;
use vova07\imperavi\Widget as ImperaviWidget;
use yii\bootstrap\Modal;
use yii\web\JsExpression;
?>
<?php Modal::begin([
	'id' => 'update-msg-dialog',
	'header' => '<h2>'.Yii::t('app/common','Update message').'</h2>',
	'footer' => Html::button(Yii::t('app/common','Save'),['class' => 'btn btn-success btn-save']),
	'size' => Modal::SIZE_LARGE,
]);?>


<?php Modal::end(); ?>
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
		<?php echo Html::hiddenInput('task_id', $taskId); ?>
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
			<div style="margin-top: -40px;" class="form-group field-crmtask-priority required">
				<div class="form-group">
					<?echo \kato\DropZone::widget([
						'id'=> 'dropzoneComment',
						'dropzoneContainer' => 'dropzoneComment',
						'previewsContainer' => 'dropzoneCommentpreview',
						'uploadUrl'=>\yii\helpers\Url::to(['/crm/task/upload-file/']),
						'options'=>
							['addRemoveLinks'=> 'true',
								'removedfile' => new JsExpression("function(file) {
                                    var name = file.name;        
                                    $.ajax({
                                        type: 'POST',
                                        url: '/service/crm/task/file-delete',
                                        data: 'id='+file.xhr.response,
                                        dataType: 'html'
                                    });
                                var _ref;
                                return (_ref = file.previewElement) != null ? _ref.parentNode.removeChild(file.previewElement) : void 0;        
                                }"),
								'thumbnailWidth'=> 90,
								'thumbnailHeight'=> 90,
								'dictDefaultMessage' => Yii::t('app/crm', 'Drop file'),
								'dictCancelUpload' => Yii::t('app/crm', 'Cancel upload'),
								'dictRemoveFile'=>Yii::t('app/crm', 'Remove file'),
							],
						'clientEvents'=>[
							'complete' => "function(file){
                                $('#dropzoneComment .dropzone-previews').append(\"<input type='hidden' name='dropZoneFiles[]' value='\"+file.xhr.response+\"'>\");                           
                            }",
						]
					]);?>
				</div>
			</div>

			<div class = "form-group">
				<button class = "btn btn-success btn-sm addCmpMsg" data = "<?=$obDialog->id;?>" type = "button">
					<?= Yii::t('app/common', 'Add message') ?>
				</button>
			</div>
		</div>
	</form>
</div>
