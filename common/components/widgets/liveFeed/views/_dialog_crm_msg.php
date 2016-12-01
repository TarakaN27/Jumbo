<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 14.12.15
 * Time: 16.05
 */
use yii\bootstrap\Html;
use vova07\imperavi\Widget as ImperaviWidget;
use yii\helpers\ArrayHelper;
use common\models\CrmTask;
use common\components\helpers\CustomViewHelper;
use yii\web\JsExpression;
if(isset($isCmpList))
$this->registerJs("
initCmptasks();
",\yii\web\View::POS_READY);
foreach ($models as $obModel)
{
	if($obModel->crm_task_id && ($obTask = $obModel->tasks))
	{
		$obModel->theme = Yii::t('app/crm','Task').' "'.Html::a($obTask->title,['/crm/task/view','id' => $obTask->id],[
				'target' => '_blank',
				'class' => 'dialog-title-link'
			]).'"';
	}
}
?>
<?foreach($models as $model):?>
<li id="dialogBlockId_<?php echo $model->id;?>" class="<?php if(in_array($model->id,$arRedisDialog)):?>dialog-not-viewed<?php endif;?>">
	<img src="/service/images/defaultUserAvatar.jpg" class="avatar" alt="Avatar">
	<div class="message_date">
		<h3 class="date text-info"><?=Date('d',$model->created_at);?></h3>
		<p class="month"><?=Date('M',$model->created_at);?></p>
	</div>
	<div class="message_wrapper">
		<h4 class="heading"><?=is_object($obUser = $model->owner) ? $obUser->getFio() : $model->buser_id;?></h4>
		<blockquote class="message">
			<?=$model->theme;?>

			<?php if(!empty($model->crm_task_id) && isset($isCmpList)):
				$deadline = ArrayHelper::getValue($model,'tasks.deadline');
				?>
				<footer>
					<span class="cmp-task-status">Статус:
						<?php
							echo Html::a(ArrayHelper::getValue($model,'tasks.statusStr'),'#',[
								'class' => 'editable',
								'data-value' => ArrayHelper::getValue($model,'tasks.status'),
								'data-type' => "select",
								'data-pk' => $model->crm_task_id,
								'data-created_by' => ArrayHelper::getValue($model,'tasks.created_by'),
								'data-source' => \yii\helpers\Json::encode(CrmTask::getStatusArr()),
								'data-url' => \yii\helpers\Url::to(['/crm/task/update-status']),
								'data-title' => Yii::t('app/common','change status')
							]);
						?>;
					</span>
					<span class="cmp-task-deadline">Крайний срок:
						<span class="fake-editable fake-datetimepicker" data-pk="<?=$model->crm_task_id?>" data-date="<?php echo empty($deadline) ? '' : Yii::$app->formatter->asDatetime($deadline) ?>">
							<?php
						echo empty($deadline) ? '' : Yii::$app->formatter->asDatetime($deadline);
						?></span>
				</footer>
			<?php endif;?>
		</blockquote>
		<br />
		<p class="url">
			<span class="fs1 text-info" aria-hidden="true" data-icon=""></span>
			<a data-id="<?=$model->id;?>" class="btn-show-hide" data-viewed="<?php if(in_array($model->id,$arRedisDialog)):?>no<?php endif;?>">
				<span><?=Yii::t('app/common','SHOW_MSG_TEXT')?></span> <i class="fa fa-chevron-down"></i>
			</a>
		</p>

	</div>
	<div class="message_wrapper ">
		<ul class="list-unstyled msg_list need-load" data-id="<?=$model->id;?>">

		</ul>
	</div>
	<div class="message_wrapper form-add-msg <?=$uniqStr?>" data-id="<?=$model->id;?>">
		<form onsubmit = "return false;" class = "msgBox" data-id = "<?=$model->id;?>">
			<?php echo Html::hiddenInput('dialog_id', $model->id); ?>
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
				<div style="margin-top: -20px;" class="form-group field-crmtask-priority required">
					<div class="form-group">
						<?echo \kato\DropZone::widget([
							'id'=> 'dropzoneComment'.$model->id,
							'dropzoneContainer' => 'dropzoneComment'.$model->id,
							'previewsContainer' => 'dropzoneCommentpreview'.$model->id,
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
                                $('#dropzoneComment".$model->id." .dropzone-previews').append(\"<input type='hidden' name='dropZoneFiles[]' value='\"+file.xhr.response+\"'>\");                           
                            }",
							]
						]);?>
					</div>
				</div>
				<br />
				<div class = "form-group">
					<button class = "btn btn-success btn-sm addCmpMsg" data = "<?=$model->id;?>" type = "button">
						<?= Yii::t('app/common', 'Add message') ?>
					</button>
				</div>
			</div>
		</form>
	</div>
</li>
<?endforeach;?>
<?php if(!is_null($pag)):?>
	<?php if($pag->getPageCount() > $pag->getPage()+1): $links = $pag->getLinks();?>
		<?=Html::button(Yii::t('app/common','Load more'),[
			'data-url' => $links[\yii\data\Pagination::LINK_NEXT],
			'class' => 'btn btn-default btn-load-more'
		])?>
	<?php endif;?>

<?endif;?>
