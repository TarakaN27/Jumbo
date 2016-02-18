<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\bootstrap\Modal;
use common\models\CrmTaskLogTime;
use common\models\CrmTask;
/* @var $this yii\web\View */
/* @var $model common\models\CrmTask */
$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/crm', 'Crm Tasks'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$this->registerJs("
    var
        URL_BEGIN_TASK = '".\yii\helpers\Url::toRoute(['begin-task'])."',
        URL_PAUSE_TASK = '".\yii\helpers\Url::toRoute(['pause-task'])."',
        URL_DONE_TASK = '".\yii\helpers\Url::toRoute(['done-task'])."',
        URL_OPEN_TASK = '".\yii\helpers\Url::toRoute(['open-task'])."',
        TASK_TIME_TRACKING = '".Yii::t('app/crm','TASK_TIME_TRACKING')."',
        CLOCK_ON_LOAD = ".($timeBegined ? 'true' : 'false').",
        TASK_TIME_TRACKING_BEGIN_SUCCESS = '".Yii::t('app/crm','TASK_TIME_TRACKING_BEGIN_SUCCESS')."',
        TASK_TIME_TRACKING_PAUSE_SUCCESS = '".Yii::t('app/crm','TASK_TIME_TRACKING_PAUSE_SUCCESS')."',
        TASK_OPEN_SUCCESS = '".Yii::t('app/crm','TASK_OPEN_SUCCESS')."',
        TASK_DONE_SUCCESS = '".Yii::t('app/crm','TASK_DONE_SUCCESS')."',
        TASK = '".Yii::t('app/crm','TASK')."'
        ;
",\yii\web\View::POS_HEAD);
$this->registerJsFile('@web/js/wm_app/task.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJs("
$('#tab_content2').on('click','.activity-update-link',
function() {
    $.post(
        '".\yii\helpers\Url::to(['update-log-time'])."',
        {
            id: $(this).attr('data-id')
        },
        function (data) {
            $('#activity-modal .modal-body').html(data);
            $('#activity-modal').modal();
        }
    );
});
$('.activity-update-link').removeClass('hidden');
",\yii\web\View::POS_READY);
$this->registerJs("

$('#activity-modal').on('beforeSubmit', 'form#EditLogWorkID', function () {
     var form = $(this);
     // return false if form still have some validation errors
     if (form.find('.has-error').length) {
          return false;
     }
     // submit form
     $.ajax({
          url: form.attr('action'),
          type: 'post',
          data: form.serialize(),
          success: function (res) {
               if(res && res.content != '')
               {
                    $('#tab_content2').html(res.content);
					addSuccessNotify(TASK,'".Yii::t('app/crm','Time successfully spent')."');
					$('#activity-modal .modal-dialog button.close').click();
					$('.user-time').html(res.timeSpend);
				    $('.activity-update-link').removeClass('hidden');
               }else{
                    $('#activity-modal .modal-body').html(res);
					addErrorNotify(TASK,'".Yii::t('app/crm','Error. Can not log time')."');
               }
          }
     });
     return false;
});
");
$this->registerJs("
	$('.project_files').on('click','.delete-link',function(){
		var
			id = $(this).attr('data-id'),
			confirmText = '".Yii::t('app/crm','Do you wont delete file')." '+$('.linkFileClass[data-id=\"'+id+'\"] span').html(),
			r = confirm(confirmText);
		if (r != true) {
		   return false;
		}
		$.ajax({
	        type: \"POST\",
	        cache: false,
	        url: '".\yii\helpers\Url::to(['delete-file'])."',
	        dataType: \"json\",
	        data: {pk:id},
	        success: function(msg){
				if(msg == 1)
				{
					$('#file-list-'+id).remove();
				}
	        },
	        error: function(msg){
	            alert('Error');
	            return false;
	        }
	    });
	});
");

?>

<?php Modal::begin([
    'id' => 'activity-modal',
    'header' => '<h2>'.Yii::t('app/crm','Edit log time').'</h2>',
    'size' => Modal::SIZE_DEFAULT,
]);?>



<?php Modal::end();
?>
<div class="row">
    <div class="col-md-12">
        <div class="x_panel">
            <div class="x_title">
                <h2><?php echo $this->title;?></h2>
                <section class="pull-right">
                    <?=  Html::a(Yii::t('app/crm', 'To list'), ['index'], ['class' => 'btn btn-warning']) ?>
                    <?= Html::a(Yii::t('app/crm', 'Create Crm Task'), ['create'], ['class' => 'btn btn-success']) ?>
                    <?php if($model->created_by == Yii::$app->user->id):?>
                        <?= Html::a(Yii::t('app/crm', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                        <?= Html::a(Yii::t('app/crm', 'Delete'), ['delete', 'id' => $model->id], [
                            'class' => 'btn btn-danger',
                            'data' => [
                                'confirm' => Yii::t('app/crm', 'Are you sure you want to delete this item?'),
                                'method' => 'post',
                            ],
                        ]) ?>
                    <?php endif;?>
                </section>
                <div class="clearfix"></div>
            </div>

            <div class="x_content">
                <div class="col-md-9 col-sm-9 col-xs-12">
                    <div class="company-header">
                        <div class="row">
                            <div class="col-md-8 col-sm-8 col-xs-12">
                            <h2><?=$model->title;?></h2>
                            <section class="horizontal-scroll">
                                <?=$model->description;?>
                            </section>
                            </div>
                            <div class="col-md-4 col-sm-4 col-xs-12">
                                <table class="table">
                                    <tr>
                                        <th><?=Yii::t('app/crm','Type')?></th>
                                        <td><?=$model->getTypeStr()?></td>
                                    </tr>
                                    <tr>
                                        <th><?=Yii::t('app/crm','Priority')?></th>
                                        <td><?=$model->getPriorityStr()?></td>
                                    </tr>
                                    <tr>
                                        <th><?= Yii::t('app/crm','Deadline');?></th>
                                        <td><?=$model->deadline;?></td>
                                    </tr>
                                    <tr>
                                        <th><?= Yii::t('app/crm','Status');?></th>
                                        <td id="taskStatusID"><?=$model->getStatusStr();?></td>
                                    </tr>
                                    <tr>
                                        <th><?= Yii::t('app/crm','Created at');?></th>
                                        <td><?=Yii::$app->formatter->asDatetime($model->created_at);?></td>
                                    </tr>
                                    <tr>
                                        <th><?= Yii::t('app/crm','Updated at');?></th>
                                        <td><?=Yii::$app->formatter->asDatetime($model->updated_at);?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="company-time-control">
                        <div class="row">
                            <div class="col-md-2 col-sm-2 col-xs-12 text-center time-block">
                                <span class="user-time">
                                    <?=\common\components\helpers\CustomHelper::getFormatedTaskTime($timeSpend)?>
                                </span> /
                                <span class="time_estimate">
                                    <?=$model->getFormatedTimeEstimate()?>
                                </span>
                            </div>
                            <div class="col-md-7 col-sm-7 col-xs-12 ">

                                    <?=Html::button(Yii::t('app/crm','Pause task'),[
                                        'class' => 'btn btn-warning pause-task '.($model->status == CrmTask::STATUS_IN_PROGRESS ? '' : 'hide'),
                                        'data-task-id' => $model->id,
                                    ])?>
                                    <?=Html::button(Yii::t('app/crm','Begin do task'),[
                                        'class' => 'btn btn-success begin-task '.($model->status == CrmTask::STATUS_OPENED ? '' : 'hide'),
                                        'data-task-id' => $model->id,
                                    ])?>
                                    <?=Html::button(Yii::t('app/crm','Done task'),[
                                        'class' => 'btn btn-danger done-task '.(
                                            in_array($model->status,[CrmTask::STATUS_IN_PROGRESS,CrmTask::STATUS_NEED_ACCEPT]) ? '' : 'hide'
                                            ),
                                        'data-task-id' => $model->id,
                                    ])?>
                                    <?=Html::button(Yii::t('app/crm','Open task'),[
                                        'class' => 'btn btn-success open-task '.(
                                            in_array($model->status,[CrmTask::STATUS_CLOSE,CrmTask::STATUS_NEED_ACCEPT]) ? '' : 'hide'
                                            ),
                                        'data-task-id' => $model->id,
                                    ])?>
                            </div>
                            <div class="col-md-2 col-sm-2 col-xs-12 ">
                                <?php
                                    if(in_array(Yii::$app->user->getLogWorkType(),[
                                        \backend\models\BUser::LOG_WORK_TYPE_TASK,
                                        \backend\models\BUser::LOG_WORK_TYPE_TIMER
                                    ])) {
                                        Modal::begin([
                                            'header' => '<h2>' . Yii::t('app/crm', 'Log work time') . '</h2>',
                                            'size' => Modal::SIZE_DEFAULT,
                                            'toggleButton' => [
                                                'tag' => 'button',
                                                'class' => 'btn btn-dark log-work',
                                                'label' => '<i class="fa fa-clock-o"></i> ' . Yii::t('app/crm', 'Log work time'),
                                            ]
                                        ]);

                                        echo $this->render('part/_form_log_work_time', [
                                            'model' => $obLogWork,
                                        ]);

                                        Modal::end();
                                    }
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="" role="tabpanel" data-example-id="togglable-tabs">
                        <ul id="myTab" class="nav nav-tabs bar_tabs" role="tablist">
                            <li role="presentation" class="active">
                                <a href="#tab_content1" id="home-tab" role="tab" data-toggle="tab" aria-expanded="true">
                                    <?=Yii::t('app/crm','Comments');?>
                                </a>
                            </li>
                            <li role="presentation" class="">
                                <a href="#tab_content2" role="tab" id="profile-tab" data-toggle="tab" aria-expanded="false">
                                    <?=Yii::t('app/crm','Spend time');?>
                                </a>
                            </li>
                            <li role="presentation" class="">
                                <a href="#tab_content3" role="tab" id="profile-tab2" data-toggle="tab" aria-expanded="false">
                                    <?=Yii::t('app/crm','History');?>
                                </a>
                            </li>
                        </ul>
                        <div id="myTabContent" class="tab-content">
                            <div role="tabpanel" class="tab-pane fade active in" id="tab_content1" aria-labelledby="home-tab">
                                <!--диалоги-->
                                <?php echo \common\components\widgets\liveFeed\LiveFeedTaskWidget::widget([
                                    'iTaskID' => $model->id
                                ]);?>
                            </div>
                            <div role="tabpanel" class="tab-pane fade" id="tab_content2" aria-labelledby="profile-tab">
                                <!-- Затраченное время -->
                                <?php echo $this->render('part/_woked_time_area',[
                                    'obLog' => $obLog,
                                    ]);
                                ?>
                            </div>
                            <div role="tabpanel" class="tab-pane fade" id="tab_content3" aria-labelledby="profile-tab">
                                <!-- история -->
                                <p>no history</p>
                            </div>
                        </div>
                    </div>


                </div>
                <!-- start project-detail sidebar -->
                <div class="col-md-3 col-sm-3 col-xs-12">
                    <?php if(!empty($model->payment_request)):?>
                        <section class="wm-side-bar-right">
                            <div class="x_title">
                                <h2><?php echo Yii::t('app/crm','Payment request')?></h2>
                                <ul class="nav navbar-right panel_toolbox">
                                    <li>
                                        <?=Html::a(Yii::t('app/crm','Follow to payment request'),
                                            ['/bookkeeping/payment-request/view','id' => $model->payment_request],
                                            [
                                            'target' => '_blank'
                                        ]);?>
                                    </li>
                                </ul>
                                <div class="clearfix"></div>
                            </div>
                        </section>
                    <?php endif;?>

                    <?php if(is_object($obCmp)):?>
                        <section class="wm-side-bar-right">
                            <div class="x_title">
                                <h2><?php echo Yii::t('app/crm','Company')?></h2>
                                <ul class="nav navbar-right panel_toolbox">
                                    <li>
                                        <?=Html::a($obCmp->getInfoWithSite(),['/crm/company/view','id' => $obCmp->id],[
                                            'target' => '_blank'
                                        ]);?>
                                    </li>
                                </ul>
                                <div class="clearfix"></div>
                            </div>
                        </section>
                    <?php endif;?>
                    <?php if(is_object($obCnt)):?>
                        <section class="wm-side-bar-right">
                            <div class="x_title">
                                <h2><?php echo Yii::t('app/crm','Contact')?></h2>
                                <ul class="nav navbar-right panel_toolbox">
                                    <li>
                                        <?=Html::a($obCnt->fio,['/crm/contact/view','id' => $obCnt->id],[
                                            'target' => '_blank'
                                        ]);?>
                                    </li>
                                </ul>
                                <div class="clearfix"></div>
                            </div>
                        </section>
                    <?php endif;?>
                    <section class="wm-side-bar-right">
                        <div class="x_title">
                            <h2><?php echo Yii::t('app/crm','Created by')?></h2>
                            <div class="clearfix"></div>
                        </div>
                        <div class="media event">
                            <a class="pull-left border-aero profile_thumb">
                                <i class="fa fa-user aero"></i>
                            </a>
                            <div class="media-body" style="height: 50px;vertical-align: middle;">
                                <p class="title"><?php echo is_object($obMan = $model->createdBy) ? $obMan->getFio() : $model->created_by;?></p>
                                <?php if(Yii::$app->user->can('superRights')):?>
                                    <p>
                                        <small><?php echo is_object($obMan = $model->createdBy) ? $obMan->getRoleStr() : 'N/A';?></small>
                                    </p>
                                <?php endif;?>
                            </div>
                        </div>
                    </section>
                    <section class="wm-side-bar-right">
                        <div class="x_title">
                            <h2><?php echo Yii::t('app/crm','Assigned At')?></h2>
                            <ul class="nav navbar-right panel_toolbox">
                                <li>
                                    <?php
                                        \common\components\customComponents\Modal\CustomModal::begin([
                                            'header' => '<h2>'.Yii::t('app/crm','Change assigned').'</h2>',
                                            'size' => Modal::SIZE_DEFAULT,
                                            'toggleButton' => [
                                                'tag' => 'a',
                                                'class' => 'link-btn-cursor',
                                                'label' => '<i class="fa fa-pencil"></i> '.Yii::t('app/crm','Change'),
                                            ]
                                        ]);

                                        echo $this->render('part/_form_change_assigned',[
                                            'model' => $model,
                                            'sAssName' => is_object($obMan = $model->assigned) ? $obMan->getFio() : $model->assigned_id,
                                        ]);

                                    \common\components\customComponents\Modal\CustomModal::end();
                                    ?>
                                </li>
                            </ul>
                            <div class="clearfix"></div>
                        </div>
                        <div class="media event">
                            <a class="pull-left border-aero profile_thumb">
                                <i class="fa fa-user aero"></i>
                            </a>
                            <div class="media-body" style="height: 50px;vertical-align: middle;">
                                <p class="title"><?php echo is_object($obMan = $model->assigned) ? $obMan->getFio() : $model->assigned;?></p>
                                <?php if(Yii::$app->user->can('superRights')):?>
                                <p>
                                    <small><?php echo is_object($obMan = $model->assigned) ? $obMan->getRoleStr() : 'N/A';?></small>
                                </p>
                                <?php endif;?>
                            </div>
                        </div>
                    </section>
                    <section class="wm-side-bar-right" >
                        <div class="x_title">
                            <h2><?php echo Yii::t('app/crm','Accomplices')?></h2>
                            <ul class="nav navbar-right panel_toolbox">
                                <li>
                                    <?php
                                    \common\components\customComponents\Modal\CustomModal::begin([
                                            'header' => '<h2>'.Yii::t('app/crm','Add accomplice').'</h2>',
                                            'size' => Modal::SIZE_DEFAULT,
                                            'toggleButton' => [
                                                'tag' => 'a',
                                                'class' => 'link-btn-cursor',
                                                'label' => '<i class="fa fa-pencil"></i> '.Yii::t('app/crm','Add'),
                                            ]
                                        ]);

                                        echo $this->render('part/_form_add_accomplice',[
                                            'model' => $obAccmpl,
                                        ]);

                                    \common\components\customComponents\Modal\CustomModal::end();
                                    ?>
                                </li>
                            </ul>
                            <div class="clearfix"></div>
                        </div>
                        <div class="media event">
                            <?php foreach($arAccmpl as $acc):?>
                                <section class="block-min-height">
                                    <a class="pull-left border-aero profile_thumb">
                                        <i class="fa fa-user aero"></i>
                                    </a>
                                    <div class="media-body" style="height: 50px;vertical-align: middle;">
                                        <p class="title"><?php echo $acc->getFio();?></p>
                                        <?php if(Yii::$app->user->can('superRights')):?>
                                        <p>
                                            <small><?php echo $acc->getRoleStr() ;?></small>
                                        </p>
                                    <?php endif;?>
                                    </div>
                                </section>
                            <?php endforeach;?>
                        </div>
                    </section>
                    <section class="wm-side-bar-right">
                        <div class="x_title">
                            <h2><?php echo Yii::t('app/crm','Watchers')?></h2>
                            <ul class="nav navbar-right panel_toolbox">
                                <li>
                                    <?php
                                    \common\components\customComponents\Modal\CustomModal::begin([
                                        'header' => '<h2>'.Yii::t('app/crm','Add watcher').'</h2>',
                                        'size' => Modal::SIZE_DEFAULT,
                                        'toggleButton' => [
                                            'tag' => 'a',
                                            'class' => 'link-btn-cursor',
                                            'label' => '<i class="fa fa-pencil"></i> '.Yii::t('app/crm','Add'),
                                        ]
                                    ]);

                                    echo $this->render('part/_form_add_watcher',[
                                        'model' => $obWatcher,
                                    ]);

                                    \common\components\customComponents\Modal\CustomModal::end();
                                    ?>
                                </li>
                            </ul>
                            <div class="clearfix"></div>
                        </div>
                        <div class="media event">
                            <?php foreach($arWatchers as $acc):?>
                                <section class="block-min-height">
                                    <a class="pull-left border-aero profile_thumb">
                                        <i class="fa fa-user aero"></i>
                                    </a>
                                    <div class="media-body" style="height: 50px;vertical-align: middle;">
                                        <p class="title"><?php echo $acc->getFio();?></p>
                                    <?php if(Yii::$app->user->can('superRights')):?>
                                        <p>
                                            <small><?php echo $acc->getRoleStr() ;?></small>
                                        </p>
                                    <?php endif;?>
                                    </div>
                                </section>
                            <?php endforeach;?>
                        </div>
                    </section>
                    <section class="panel wm-side-bar-right">
                        <div class="x_title">
                            <h2><?php echo Yii::t('app/crm','Task files')?></h2>
                            <ul class="nav navbar-right panel_toolbox">
                                <li>
                                    <?php
                                    Modal::begin([
                                        'header' => '<h2>'.Yii::t('app/crm','Adding a file').'</h2>',
                                        'size' => Modal::SIZE_LARGE,
                                        'toggleButton' => [
                                            'tag' => 'a',
                                            'class' => 'link-btn-cursor',
                                            'label' => '<i class="fa fa-plus"></i> '.Yii::t('app/crm','Add file'),
                                        ]
                                    ]);
                                    echo $this->render('part/_part_form_file',['model' => $obFile]);
                                    Modal::end();
                                    ?>
                                </li>
                            </ul>
                            <div class="clearfix"></div>
                        </div>
                        <div class="panel-body event">
                            <?php if(empty($arFile)):?>
                                <?=Yii::t('app/crm','No crm file')?>
                            <?php else:?>
                                <ul class="list-unstyled project_files">
                                    <?php foreach($arFile as $file):?>
                                        <li id="file-list-<?=$file->id;?>">
                                            <a class="linkFileClass" href="<?=\yii\helpers\Url::to(['download-file','id' => $file->id])?>" data-id="<?=$file->id;?>" target="_blank">
                                                <i class="<?=$file->getHtmlClassExt();?>"></i>
                                                <span><?=$file->getSplitName();?></span>
                                            </a>
                                            <a class="delete-link pull-right" data-id="<?=$file->id;?>"><i class="fa fa-close"></i></a>
                                        </li>
                                    <?php endforeach;?>
                                </ul>
                            <?php endif;?>
                        </div>
                    </section>
                </div>
                <!-- end project-detail sidebar -->
            </div>
        </div>
    </div>
</div>

