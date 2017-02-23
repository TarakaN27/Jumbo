<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 11.12.15
 * Time: 14.46
 */
use yii\bootstrap\Modal;
use common\components\customComponents\collapse\CollapseWidget;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;
use common\models\CUser;
$this->title = $model->getInfo();
//скрипты для редактирования контактов
if(!empty($arContacts)) {
	$this->registerCssFile('//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/css/bootstrap-editable.css');
	$this->registerJsFile(
		'//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/js/bootstrap-editable.min.js',
		['depends' => [
			'yii\web\JqueryAsset',
			'yii\web\YiiAsset',
			'yii\bootstrap\BootstrapPluginAsset',
		]]
	);
	$this->registerJs("
		$('.editable').editable({
		    clear: false,
		    validate: function(value) {
				if($.trim(value) == '') {
					return 'This field is required';
				}
			}
		});
	", \yii\web\View::POS_READY);
}

$this->registerJs("
	var
			link_hash = window.location.hash; //получаем якорь из урл

	if(link_hash != undefined && link_hash != '')
		{
			$('#myTab a[href=\"'+link_hash+'\"]').tab('show'); //выбираем нужный tab
		}
",\yii\web\View::POS_READY);

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
")
?>
<div class="row">
	<div class="col-md-12">
		<div class="x_panel">
			<div class="x_title">
				<h2><?php echo $this->title;?>
					<?php if($model->archive == CUser::ARCHIVE_YES):?>
					<small class="red">(<?=Yii::t('app/crm','Company in archive')?>)</small>
					<?php endif;?>
				</h2>

				<section class="pull-right">
				<?=  Html::a(Yii::t('app/crm', 'To list'), ['index'], ['class' => 'btn btn-warning']) ?>
				<?php if(
				Yii::$app->user->can('adminRights') ||
				Yii::$app->user->can('only_bookkeeper') ||
				Yii::$app->user->can('only_manager') ||
				Yii::$app->user->can('only_jurist')
				):?>
				<?= Html::a('<i class="fa fa-plus"></i> '.Yii::t('app/crm', 'Create company'), ['create'], ['class' => 'btn btn-success']) ?>
				<?php if(Yii::$app->user->crmCanEditModel(
					$model,
					'created_by',
					'manager_id',
					'is_opened'
				)):?>
				<?= Html::a('<i class="fa fa-pencil"></i> '.Yii::t('app/crm', 'Update'), ['update', 'id' => $model->id],[
						'class' => 'btn btn-primary',
						'title' => Yii::t('yii', 'Update'),
						'aria-label' => Yii::t('yii', 'Update'),
						'data-pjax' => '0',
					]) ?>
				<?php endif;?>

				<?php

				$str = $model->archive != CUser::ARCHIVE_YES ? Yii::t('app/crm','Archive') :Yii::t('app/crm','Return from archive');
				if(Yii::$app->user->crmCanDeleteModel(
					$model,
					'created_by',
					'manager_id',
					'is_opened'
				)):?>

					<?=Html::a('<i class="fa fa-archive"></i> '.$str ,['archive','id' => $model->id],[
						'class' => 'btn btn-primary',
						'title' => Yii::t('app/crm', 'Archive'),
						'aria-label' => Yii::t('app/crm', 'Archive'),
						'data-pjax' => '0',
					]);?>
				<?php endif;?>
				<?php endif; ?>
				<?php if(Yii::$app->user->can('adminRights')):?>
				<?= Html::a('<i class="fa fa-trash"></i> '.Yii::t('app/crm', 'Delete'), ['delete', 'id' => $model->id], [
						'title' => Yii::t('yii', 'Delete'),
						'aria-label' => Yii::t('yii', 'Delete'),
						'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
						'data-method' => 'post',
						'data-pjax' => '0',
						'class' => 'btn btn-danger',
					]) ?>
				<?php endif;?>
				</section>


				<div class="clearfix"></div>
			</div>

			<div class="x_content">
				<div class="col-md-9 col-sm-9 col-xs-12">
					<div class="company-header">
						<div class="row">
							<div class="col-md-6 col-sm-6 col-xs-12">
								<table>
									<?php if(
									Yii::$app->user->getIdentity()->role != \backend\models\BUser::ROLE_USER
									):?>
									<tr>
										<th><?=YII::t('app/crm','FIO')?>: <th>
										<td><?=is_object($obRequisite) ? $obRequisite->getContactFIO() : '';?></td>
									</tr>
									<?php endif;?>
									<tr>
										<th><?=YII::t('app/crm','Type')?>: <th>
										<td><?=is_object($obType = $model->userType) ? $obType->name : '';?></td>
									</tr>
									<tr>
										<th><?=YII::t('app/crm','Prospects')?>: <th>
										<td><?=is_object($obPr = $model->prospects) ? $obPr->name : '';?></td>
									</tr>
								</table>
							</div>
							<div class="col-md-6 col-sm-6 col-xs-12">
								<table>
									<?php if(
									Yii::$app->user->getIdentity()->role != \backend\models\BUser::ROLE_USER
									):?>
									<tr>
										<th><?=YII::t('app/crm','Phone')?>: </th>
										<td><?=is_object($obRequisite) ? $obRequisite->c_phone : '';?></td>
									</tr>
									<tr>
										<th><?=YII::t('app/crm','Email')?>:</th>
										<td><?=is_object($obRequisite) ? $obRequisite->c_email : '';?></td>
									</tr>
									<?php endif;?>
									<tr>
										<th><?=YII::t('app/crm','Site')?>: </th>
										<td><?=is_object($obRequisite) ? '<a target="_blank" href="'.$obRequisite->getSiteUrl().'">'.$obRequisite->site.'</a>' : '';?></td>
									</tr>
									<tr>
										<th><?=Yii::t('app/crm','Description')?>: </th>
										<td><?=is_object($obRequisite) ? $obRequisite->description : '';?></td>
									</tr>
									<?php if($obQHour):?>
										<tr>
											<th><?=YII::t('app/crm','Quantity hours')?>: </th>
											<td><?php
												$hours = empty($obQHour->hours) ? 0 : $obQHour->hours;
												$spent = empty($obQHour->spent_time) ? 0 : $obQHour->spent_time;
												$item = $hours-$spent;

												$spanOpt = [];
												if($item < 0)
													$spanOpt = ['class' => 'ts_red'];
												else
													$spanOpt = ['class' => 'ts_green'];

												echo Html::tag('span',$item,$spanOpt);

												?></td>
										</tr>
									<?php endif;?>
									<?php
										$addFields = $model->getDisplayEntityValues();
										if(!empty($addFields))
											foreach($addFields as $field):
									?>
										<tr>
											<th><?=$field['name']?>: </th>
											<td><?=$field['value'];?></td>

										</tr>
									<?php endforeach;?>
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
								<?php echo \common\components\widgets\liveFeed\LiveFeedCompanyWidget::widget(['iCmpID' => $model->id]);?>
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
									'contactDesc' => $contactDesc,
									'dataContact' => $dataContact,
									'sAssName' => $sAssName,
									'data' => $data,
									'hideCuser' => TRUE,
									'pTaskName' => '',
                                    'dataWatchers' => [],
									'obTaskRepeat' => $obTaskRepeat
								])?>
							</div>
						</div>
					</div>
				</div>
				<!-- start project-detail sidebar -->
				<div class="col-md-3 col-sm-3 col-xs-12">
					<!--Группа компаний-->
					<?php if(!empty($arGroups)):?>
					<section>
						<div class="x_title">
							<h2><?php echo Yii::t('app/crm','Group company')?></h2>
							<div class="clearfix"></div>
						</div>
						<div class="media event">
							<?php
							$arItem = [];
							foreach($arGroups as $group)
							{
								$arItem [] = [
									'label' => $group->name,
									'content'=>$this->render('_part_collapse_groups',['data' => $group->cuserObjects])
								];
							}
							?>
							<?php echo CollapseWidget::widget([
								'items' => $arItem
							]);
							?>

						</div>
					</section>
					<?php endif;?>
					<!--End группа компаний-->
					<!--ответственный-->
					<section class="wm-side-bar-right">
						<div class="x_title">
							<h2><?php echo Yii::t('app/crm','Assigned At')?></h2>
							<ul class="nav navbar-right panel_toolbox">
								<li>
									<?php
									if(Yii::$app->user->can('adminRights') || $model->manager_id == Yii::$app->user->id) {
										\common\components\customComponents\Modal\CustomModal::begin([
											'header' => '<h2>' . Yii::t('app/crm', 'Change assigned') . '</h2>',
											'size' => Modal::SIZE_DEFAULT,
											'toggleButton' => [
												'tag' => 'a',
												'class' => 'link-btn-cursor',
												'label' => '<i class="fa fa-pencil"></i> ' . Yii::t('app/crm', 'Change'),
											]
										]);
										echo $this->render('_part_form_change_assigned', [
											'model' => $model,
											'sAssName' => is_object($obMan = $model->manager) ? $obMan->getFio() : $model->manager_id
										]);
										\common\components\customComponents\Modal\CustomModal::end();
									}
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
								<p class="title"><?php echo is_object($obMan = $model->manager) ? $obMan->getFio() : $model->manager_id;?></p>
								<?php if(Yii::$app->user->can('superRights')):?>
									<p> <small><?php echo is_object($obMan = $model->manager) ? $obMan->getRoleStr() : 'N/A';?></small>
								<?php endif;?>
								</p>
							</div>
						</div>
					</section>
					<!--End ответсвенный-->
					<!--Ответственный специалист CPC -->
                    <section class="wm-side-bar-right">
                        <div class="x_title">
                            <h2><?php echo Yii::t('app/users','CRC manager')?></h2>
                            <ul class="nav navbar-right panel_toolbox">
                                <li>
                                    <?php
                                    if(Yii::$app->user->can('adminRights') || Yii::$app->user->can('teamlead')) {
                                        \common\components\customComponents\Modal\CustomModal::begin([
                                            'header' => '<h2>' . Yii::t('app/crm', 'Change assigned') . '</h2>',
                                            'size' => Modal::SIZE_DEFAULT,
                                            'toggleButton' => [
                                                'tag' => 'a',
                                                'class' => 'link-btn-cursor',
                                                'label' => '<i class="fa fa-pencil"></i> ' . Yii::t('app/crm', 'Change'),
                                            ]
                                        ]);
                                        echo $this->render('_part_form_change_assigned_cpc', [
                                            'model' => $model,
                                            'sAssName' => is_object($obMan = $model->managerCrc) ? $obMan->getFio() : $model->manager_crc_id
                                        ]);
                                        \common\components\customComponents\Modal\CustomModal::end();
                                    }
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
                                <p class="title"><?php echo is_object($obMan = $model->managerCrc) ? $obMan->getFio() : $model->manager_crc_id;?></p>
                                <?php if(Yii::$app->user->can('superRights')):?>
                                <p> <small><?php echo is_object($obMan = $model->managerCrc) ? $obMan->getRoleStr() : 'N/A';?></small>
                                    <?php endif;?>
                                </p>
                            </div>
                        </div>
                    </section>
					<!-- end ответственный специалист-->

					<?php if(
						Yii::$app->user->getIdentity()->role != \backend\models\BUser::ROLE_USER
					):?>
					<section class="wm-side-bar-right">
						<div class="x_title">
							<h2><?php echo Yii::t('app/crm','Contacts')?></h2>
							<ul class="nav navbar-right panel_toolbox">
								<li>
										<?php
											Modal::begin([
												'header' => '<h2>'.Yii::t('app/crm','Quick adding a contact').'</h2>',
												'size' => Modal::SIZE_LARGE,
												'toggleButton' => [
													'tag' => 'a',
													'class' => 'link-btn-cursor',
													'label' => '<i class="fa fa-plus"></i> '.Yii::t('app/crm','Add contact'),
												]
											]);
											echo $this->render('_part_form_contact',['model' => $obModelContact]);
											Modal::end();
										?>
								</li>
							</ul>
							<div class="clearfix"></div>
						</div>
						<div class="media event">
							<?php if(!empty($arContacts)):?>

							<?php
								$arItem = [];
								foreach($arContacts as $contact)
								{
									$arItem [] = [
										'label' => $contact->fio.'('.$contact->post.') '.Html::a(
												'<i class="fa fa-pencil"></i>',
												['/crm/contact/update','id' => $contact->id],
												[
													'class' => 'pull-right',
													'target' => '_blank'
												]),
										'content'=>$this->render('_part_collapse_contact',['contact' => $contact])
									];
								}
							?>
							<?php echo CollapseWidget::widget([
									'encodeLabels' => false,
									'items' => $arItem
								]);
							?>

							<?php else:?>
								<?=Yii::t('app/crm','No contacts');?>
							<?php endif;?>
							<br />
						</div>
					</section>
					<?php endif;?>
					<section class="panel wm-side-bar-right">
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
										echo $this->render('_part_form_file',['model' => $obFile]);
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
