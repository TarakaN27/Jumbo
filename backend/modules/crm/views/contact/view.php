<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\bootstrap\Modal;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $model common\models\CrmCmpContacts */

$this->title = Yii::t('app/crm','Contact').' №'.$model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/crm', 'Crm Cmp Contacts'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
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
$this->registerJs("
	var
			link_hash = window.location.hash; //получаем якорь из урл

	if(link_hash != undefined && link_hash != '')
		{
			$('#myTab a[href=\"'+link_hash+'\"]').tab('show'); //выбираем нужный tab
		}
",\yii\web\View::POS_READY);
?>
<div class="row">
    <div class="col-md-12">
        <div class="x_panel">
            <div class="x_title">
                <h2><?php echo $this->title;?></h2>
                <section class="pull-right">
                    <?=  Html::a(Yii::t('app/crm', 'To list'), ['index'], ['class' => 'btn btn-warning']) ?>
                    <?= Html::a(Yii::t('app/crm', 'Create Crm Cmp Contacts'), ['create'], ['class' => 'btn btn-success']) ?>
                    <?= Html::a(Yii::t('app/crm', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                    <?= Html::a(Yii::t('app/crm', 'Delete'), ['delete', 'id' => $model->id], [
                        'class' => 'btn btn-danger',
                        'data' => [
                            'confirm' => Yii::t('app/crm', 'Are you sure you want to delete this item?'),
                            'method' => 'post',
                        ],
                    ]) ?>
                </section>
                <div class="clearfix"></div>
            </div>

            <div class="x_content">
                <div class="col-md-9 col-sm-9 col-xs-12">
                    <div class="company-header">
                        <div class="row">
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <table class="table">
                                    <tr>
                                        <th><?=YII::t('app/crm','FIO')?>:</th>
                                        <td><?=$model->fio;?></td>
                                    </tr>
                                    <tr>
                                        <th><?=YII::t('app/crm','Contact type')?>:</th>
                                        <td><?=$model->getTypeStr();?></td>
                                    </tr>
                                    <tr>
                                        <th><?=YII::t('app/crm','Company')?>:</th>
                                        <td><?=is_object($obCmp = $model->cmp) ? $obCmp->getInfo() : $model->cmp_id;?></td>
                                    </tr>
                                    <tr>
                                        <th><?=YII::t('app/crm','Description')?>:</th>
                                        <td><?=$model->description;?></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <table class="table">
                                    <tr>
                                        <th><?=YII::t('app/crm','Post')?>:</th>
                                        <td><?$model->post;?></td>
                                    </tr>
                                    <tr>
                                        <th><?=YII::t('app/crm','Phone')?>:</th>
                                        <td><?=$model->phone;?></td>
                                    </tr>
                                    <tr>
                                        <th><?=YII::t('app/crm','Email')?>:</th>
                                        <td><?=$model->email;?></td>
                                    </tr>
                                    <tr>
                                        <th><?=YII::t('app/crm','Addition info')?>:</th>
                                        <td><?=$model->addition_info;?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="" role="tabpanel" data-example-id="togglable-tabs">
                        <ul id="myTab" class="nav nav-tabs bar_tabs" role="tablist">
                            <li role="presentation" class="active">
                                <a href="#tab_content1" id="home-tab" role="tab" data-toggle="tab" aria-expanded="true">
                                    <?=Yii::t('app/crm','Recent Activity');?>
                                </a>
                            </li>
                            <li role="presentation" class="">
                                <a href="#tab_content2" role="tab" id="profile-tab" data-toggle="tab" aria-expanded="false">
                                    <?=Yii::t('app/crm','Tasks');?>
                                </a>
                            </li>
                            <li role="presentation" class="">
                                <a href="#tab_content3" role="tab" id="profile-tab2" data-toggle="tab" aria-expanded="false">
                                    <?=Yii::t('app/crm','History');?>
                                </a>
                            </li>
                            <li role="presentation" class="wm_right_tab">
                                <a href="#tab_content4" role="tab" id="profile-tab4" data-toggle="tab" aria-expanded="false">
                                    <h3 class="label label-primary"><?=Yii::t('app/crm','Add task');?></h3>
                                </a>
                            </li>
                        </ul>
                        <div id="myTabContent" class="tab-content">
                            <div role="tabpanel" class="tab-pane fade active in" id="tab_content1" aria-labelledby="home-tab">
                                <?= \common\components\widgets\liveFeed\LiveFeedContactWidget::widget(['iCntID' => $model->id])?>
                            </div>
                            <div role="tabpanel" class="tab-pane fade" id="tab_content2" aria-labelledby="profile-tab">
                                <?php Pjax::begin();?>
                                <?= GridView::widget([
                                    'dataProvider' => 	$dataProviderTask,
                                    //'filterModel' => $searchModel,
                                    'columns' => [
                                        ['class' => 'yii\grid\SerialColumn'],
                                        [
                                            'attribute' => 'title',
                                            'format' => 'html',
                                            'value' => function($model){
                                                return Html::a($model->title,['/crm/task/view','id' => $model->id],['class' => 'link-upd']);
                                            }
                                        ],
                                        [
                                            'attribute' => 'type',
                                            'value' => function($model){
                                                return $model->getTypeStr();
                                            },
                                            'filter' => \common\models\CrmTask::getTypeArr()
                                        ],
                                        [
                                            'attribute' => 'contact_id',
                                            'value' => function($model){
                                                return is_object($obCnt = $model->contact) ? $obCnt->fio : $model->contact_id;
                                            }
                                        ],

                                        [
                                            'attribute' => 'deadline',
                                        ],
                                        [
                                            'attribute' => 'priority',
                                            'value' => function($model){
                                                return $model->getPriorityStr();
                                            },
                                            'filter' => \common\models\CrmTask::getPriorityArr()
                                        ],
                                        [
                                            'attribute' => 'status',
                                            'value' => function($model){
                                                return $model->getStatusStr();
                                            }
                                        ]
                                    ],
                                ]); ?>
                                <?php Pjax::end();?>
                            </div>
                            <div role="tabpanel" class="tab-pane fade" id="tab_content3" aria-labelledby="profile-tab">
                                <p>no history</p>
                            </div>
                            <div role="tabpanel" class="tab-pane fade" id="tab_content4" aria-labelledby="profile-tab">
                                <?php echo $this->render('../task/_form',[
                                    'model' => $modelTask,
                                    'sAssName' => $sAssName,
                                    'data' => $data,
                                    'hideContact' => TRUE
                                ])?>
                            </div>
                        </div>
                    </div>


                </div>
                <!-- start project-detail sidebar -->
                <div class="col-md-3 col-sm-3 col-xs-12">
                    <section>
                        <div class="x_title">
                            <h2><?php echo Yii::t('app/crm','Assigned At')?></h2>
                            <ul class="nav navbar-right panel_toolbox">
                                <li>
                                    <?php
                                    Modal::begin([
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
                                            'buserDesc' => is_object($obMan = $model->assignedAt) ? $obMan->getFio() : $model->assigned_at
                                        ]);
                                    Modal::end();
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
                                <p class="title"><?php echo is_object($obMan = $model->assignedAt) ? $obMan->getFio() : $model->assigned_at;?></p>
                                <?php if(Yii::$app->user->can('superRights')):?>
                                    <p> <small><?php echo is_object($obMan = $model->assignedAt) ? $obMan->getRoleStr() : 'N/A';?></small>
                                <?php endif;?>
                                </p>
                            </div>
                        </div>
                    </section>
                    <section class="panel">
                        <div class="x_title">
                            <h2><?php echo Yii::t('app/crm','Project files')?></h2>
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
                                            echo $this->render('part/_form_file',['model' => $obFile]);
                                        Modal::end();
                                    ?>
                                </li>
                            </ul>
                            <div class="clearfix"></div>
                        </div>
                        <div class="panel-body">
                            <?php if(empty($arFiles)):?>
                                <?=Yii::t('app/crm','No crm file')?>
                            <?php else:?>
                                <ul class="list-unstyled project_files">
                                    <?php foreach($arFiles as $file):?>
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