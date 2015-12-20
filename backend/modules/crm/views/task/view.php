<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\bootstrap\Modal;
/* @var $this yii\web\View */
/* @var $model common\models\CrmTask */
/**
 * 'title',
'description:ntext',
'deadline',
'priority',
'type',
'task_control',
'parent_id',
'assigned_id',
'created_by',
'time_estimate:datetime',
'status',
'date_start',
'duration_fact',
'closed_by',
'closed_date',
'cmp_id',
'contact_id',
'dialog_id',
'created_at',
'updated_at',
 */
$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/crm', 'Crm Tasks'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-md-12">
        <div class="x_panel">
            <div class="x_title">
                <h2><?php echo $this->title;?></h2>
                <section class="pull-right">
                    <?=  Html::a(Yii::t('app/crm', 'To list'), ['index'], ['class' => 'btn btn-warning']) ?>
                    <?= Html::a(Yii::t('app/crm', 'Create Crm Task'), ['create'], ['class' => 'btn btn-success']) ?>
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
                            <div class="col-md-8 col-sm-8 col-xs-12">
                            <h2><?=$model->title;?></h2>
                            <section>
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
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="company-time-control">
                        <div class="row">
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <span class="user-time" data-spend="<?=$timeSpend?>" data-begined="<?=$timeBegined?>">
                                    <?=\common\components\helpers\CustomHelper::getFormatedTaskTime($timeSpend+$timeBegined)?>
                                </span>/
                                <span class="time_estimate">
                                    <?=$model->getFormatedTimeEstimate()?>
                                </span>
                            </div>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <?php if($timeBegined):?>
                                    <?=Html::button(Yii::t('app/crm','Pause task'),['class' => 'btn btn-success'])?>
                                <?php else:?>
                                    <?=Html::button(Yii::t('app/crm','Begin do task'),['class' => 'btn btn-success'])?>
                                <?php endif;?>
                                <?=Html::button(Yii::t('app/crm','Done task'),['class' => 'btn btn-danger'])?>
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
                                <?php echo \common\components\widgets\liveFeed\LiveFeedTaskWidget::widget([
                                    'iDialogID' => $model->dialog_id
                                ]);?>
                            </div>
                            <div role="tabpanel" class="tab-pane fade" id="tab_content2" aria-labelledby="profile-tab">
                                <!-- start user projects -->
                                <table class="data table table-striped no-margin">
                                    <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Project Name</th>
                                        <th>Client Company</th>
                                        <th class="hidden-phone">Hours Spent</th>
                                        <th>Contribution</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td>1</td>
                                        <td>New Company Takeover Review</td>
                                        <td>Deveint Inc</td>
                                        <td class="hidden-phone">18</td>
                                        <td class="vertical-align-mid">
                                            <div class="progress">
                                                <div class="progress-bar progress-bar-success" data-transitiongoal="35"></div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>2</td>
                                        <td>New Partner Contracts Consultanci</td>
                                        <td>Deveint Inc</td>
                                        <td class="hidden-phone">13</td>
                                        <td class="vertical-align-mid">
                                            <div class="progress">
                                                <div class="progress-bar progress-bar-danger" data-transitiongoal="15"></div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>3</td>
                                        <td>Partners and Inverstors report</td>
                                        <td>Deveint Inc</td>
                                        <td class="hidden-phone">30</td>
                                        <td class="vertical-align-mid">
                                            <div class="progress">
                                                <div class="progress-bar progress-bar-success" data-transitiongoal="45"></div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>4</td>
                                        <td>New Company Takeover Review</td>
                                        <td>Deveint Inc</td>
                                        <td class="hidden-phone">28</td>
                                        <td class="vertical-align-mid">
                                            <div class="progress">
                                                <div class="progress-bar progress-bar-success" data-transitiongoal="75"></div>
                                            </div>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                                <!-- end user projects -->

                            </div>
                            <div role="tabpanel" class="tab-pane fade" id="tab_content3" aria-labelledby="profile-tab">
                                <p>xxFood truck fixie locavore, accusamus mcsweeney's marfa nulla single-origin coffee squid. Exercitation +1 labore velit, blog sartorial PBR leggings next level wes anderson artisan four loko farm-to-table craft beer twee. Qui photo booth letterpress, commodo enim craft beer mlkshk </p>
                            </div>
                        </div>
                    </div>


                </div>
                <!-- start project-detail sidebar -->
                <div class="col-md-3 col-sm-3 col-xs-12">
                    <section>
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
                                <p>
                                    <small><?php echo is_object($obMan = $model->createdBy) ? $obMan->getRoleStr() : 'N/A';?></small>
                                </p>
                            </div>
                        </div>
                    </section>
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
                                            'buserDesc' => is_object($obMan = $model->assigned) ? $obMan->getFio() : $model->assigned_at
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
                                <p class="title"><?php echo is_object($obMan = $model->assigned) ? $obMan->getFio() : $model->assigned;?></p>
                                <p>
                                    <small><?php echo is_object($obMan = $model->assigned) ? $obMan->getRoleStr() : 'N/A';?></small>
                                </p>
                            </div>
                        </div>
                    </section>
                    <section>
                        <div class="x_title">
                            <h2><?php echo Yii::t('app/crm','Accomplices')?></h2>
                            <ul class="nav navbar-right panel_toolbox">
                                <li>
                                    <?php
                                        Modal::begin([
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

                                        Modal::end();
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
                                        <p>
                                            <small><?php echo $acc->getRoleStr() ;?></small>
                                        </p>
                                    </div>
                                </section>
                            <?php endforeach;?>
                        </div>
                    </section>
                    <section>
                        <div class="x_title">
                            <h2><?php echo Yii::t('app/crm','Watchers')?></h2>
                            <ul class="nav navbar-right panel_toolbox">
                                <li>
                                    <?php
                                    Modal::begin([
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

                                    Modal::end();
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
                                        <p>
                                            <small><?php echo $acc->getRoleStr() ;?></small>
                                        </p>
                                    </div>
                                </section>
                            <?php endforeach;?>
                        </div>
                    </section>
                </div>
                <!-- end project-detail sidebar -->
            </div>
        </div>
    </div>
</div>

