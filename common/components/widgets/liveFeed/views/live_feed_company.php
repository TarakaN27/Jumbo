<?php
use yii\helpers\Html;
use kartik\select2\Select2;
use \vova07\imperavi\Widget as ImperaviWidget;
use yii\bootstrap\Modal;
use common\models\CrmTask;
use common\components\helpers\CustomViewHelper;
$this->registerCssFile('//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/css/bootstrap-editable.css');
CustomViewHelper::registerJsFileWithDependency('@web/js/moment.min.js',$this);
$this->registerJsFile(
	'//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/js/bootstrap-editable.min.js',
	['depends' => [
		'yii\web\JqueryAsset',
		'yii\web\YiiAsset',
		'yii\bootstrap\BootstrapPluginAsset',
	]]
);
$this->registerJs("
function initCmptasks()
{
$('.editable').editable({
    clear: false,
    source: ".\yii\helpers\Json::encode(CrmTask::getStatusArr()).",
    validate: function(value) {
		if($.trim(value) == '') {
			return 'This field is required';
		}
	},
});

function getAvailableStatus(value,createdBy)
{
    var
        value = parseInt(value),
        arStatus = [];
    switch (value) {
      case ".CrmTask::STATUS_OPENED.":
        arStatus = [".CrmTask::STATUS_IN_PROGRESS.",value];
        break
      case ".CrmTask::STATUS_IN_PROGRESS.":
        arStatus = [".CrmTask::STATUS_OPENED.",".CrmTask::STATUS_CLOSE.",value];
        break
      case ".CrmTask::STATUS_CLOSE.":
        arStatus = [".CrmTask::STATUS_OPENED.",value];
        break
      case ".CrmTask::STATUS_NEED_ACCEPT.":
        if(parseInt(createdBy) == ".Yii::$app->user->id.")
        {
            arStatus = [".CrmTask::STATUS_OPENED.",".CrmTask::STATUS_CLOSE.",value];
        }else{
            arStatus = [".CrmTask::STATUS_OPENED.",value];
        }
        break
      default:
        break;
    }

    return arStatus;
}

$('.editable').on('shown', function(e, editableObj) {

    var
        value = editableObj.input.\$input.val(),
        createdBy = $(this).attr('data-created_by'),
        arAvSts = getAvailableStatus(value,createdBy),
        id = $(this).attr('aria-describedby');

    $('#'+id).find('option').each(function(){

        if(jQuery.inArray( parseInt($( this ).attr('value')), arAvSts ) == -1)
        {
            $(this).addClass('hide');
        }else{
            $(this).removeClass('hide');
        }
    });
});

$('.editable').on('save', function(e, params) {
    var
        pk = $(this).data('editable').options.pk;
    if(parseInt(params.newValue) == ".CrmTask::STATUS_CLOSE.")
    {
        $('.x_content tr[data-key=\"'+pk+'\"] .link-upd').addClass('line-through');
    }else{
         $('.x_content tr[data-key=\"'+pk+'\"] .link-upd').removeClass('line-through');
    }
});
$('.fake-datetimepicker').datetimepicker({
        format: 'dd.MM.yyyy hh:ii',
        autoclose:true,
        maxView: 3,
    	minuteStep: 30,
    	endDate: new Date()
    }).on('changeDate', function(ev){
		var 
			TimeZoned = new Date(ev.date.setTime(ev.date.getTime() + (ev.date.getTimezoneOffset() * 60000)));
			
		var 
			this1 = this,
			date = moment(TimeZoned); //Get the current date
			
    	$.ajax({
            type: \"POST\",
            cache: false,
            url: '".\yii\helpers\Url::to(['/ajax-service/task-deadline'])."',
            dataType: \"json\",
            data: {date: date.format('DD.MM.YYYY HH:mm'),pk:$(this1).attr('data-pk')},
            success: function (data) {
               $(this1).html(date.format('DD.MM.YYYY HH:mm'));
            },
            error: function (msg) {
                addErrorNotify('Изменение крайнего срока задачи', 'Не удалось выполнить запрос!');
                return false;
            }
        });
});

};

",\yii\web\View::POS_HEAD);

?>

<?php Modal::begin([
	'id' => 'cmp-list-task-upd-status',
	'header' => '<h2>'.Yii::t('app/common','Update task status').'</h2>',
	'footer' => Html::button(Yii::t('app/common','Save'),['class' => 'btn btn-success btn-save cmp-task-save-status']),
	'size' => Modal::SIZE_LARGE,
]);?>
<?=Html::hiddenInput('cmp-task-id',NULL,['id' => 'cmp-task-status-id'])?>
<p>Задача: <span class="task_name"></span></p>
<p>Текущий статус: <span class="task_status"></span></p>
<?=Html::dropDownList('task_new_status',NULL,\common\models\CrmTask::getStatusArr(),[
	'id' => 'crm_list_task_status',
	'class' => 'form-control'
])?>
<?php Modal::end();?>

<?php Modal::begin([
	'id' => 'update-msg-dialog',
	'header' => '<h2>'.Yii::t('app/common','Update message').'</h2>',
	'footer' => Html::button(Yii::t('app/common','Save'),['class' => 'btn btn-success btn-save']),
	'size' => Modal::SIZE_LARGE,
]);?>
<?php Modal::end(); ?>
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
			<?php
			/** @var \common\models\Dialogs $obModel */
			foreach ($obModels as $obModel)
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
			<?= $this->render('_dialog_crm_msg', [
			'models' => $obModels,
			'pag' => $pagination,
			'uniqStr' => 'dummy_'.$iCmpID,
			'arRedisDialog' => $arRedisDialog,
			'isCmpList' => TRUE
		]) ?>
	<?php else:?>
		<p class="emptyDialog"><?php echo Yii::t('app/crm','No dialogs at feed')?>
	<?php endif;?>
</ul>
<!-- end recent activity -->
