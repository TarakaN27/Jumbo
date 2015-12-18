<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 11.12.15
 * Time: 14.46
 */
use yii\bootstrap\Modal;
use common\components\customComponents\collapse\CollapseWidget;

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
				<h2><?php echo $this->title;?></h2>
				<ul class="nav navbar-right panel_toolbox">
					<li><a href="#"><i class="fa fa-chevron-up"></i></a>
					</li>
					<li class="dropdown">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><i class="fa fa-wrench"></i></a>
						<ul class="dropdown-menu" role="menu">
							<li><a href="#">Settings 1</a>
							</li>
							<li><a href="#">Settings 2</a>
							</li>
						</ul>
					</li>
					<li><a href="#"><i class="fa fa-close"></i></a>
					</li>
				</ul>
				<div class="clearfix"></div>
			</div>

			<div class="x_content">
				<div class="col-md-9 col-sm-9 col-xs-12">
					<div class="company-header">
						<div class="row">
							<div class="col-md-6 col-sm-6 col-xs-12">
								<table>
									<tr>
										<th><?=YII::t('app/crm','Company type')?>:<th>
										<td><?=is_object($userType = $model->userType) ? $userType->name : $model->type;?></td>
									</tr>
									<tr>
										<th><?=YII::t('app/crm','FIO')?>:<th>
										<td><?=is_object($obRequisite) ? $obRequisite->getContactFIO() : '';?></td>
									</tr>
								</table>
							</div>
							<div class="col-md-6 col-sm-6 col-xs-12">
								<table>
									<tr>
										<th><?=YII::t('app/crm','Phone')?>:</th>
										<td><?=is_object($obRequisite) ? $obRequisite->c_phone : '';?></td>
									</tr>
									<tr>
										<th><?=YII::t('app/crm','Email')?>:</th>
										<td><?=is_object($obRequisite) ? $obRequisite->c_email : '';?></td>
									</tr>
									<tr>
										<th><?=YII::t('app/crm','Site')?>:</th>
										<td><?=is_object($obRequisite) ? $obRequisite->site : '';?></td>
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
						</ul>
						<div id="myTabContent" class="tab-content">
							<div role="tabpanel" class="tab-pane fade active in" id="tab_content1" aria-labelledby="home-tab">
								<?php echo \common\components\widgets\liveFeed\LiveFeedCompanyWidget::widget(['iCmpID' => $model->id]);?>
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
									echo $this->render('_part_form_change_assigned',['model' => $model]);
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
								<p class="title"><?php echo is_object($obMan = $model->manager) ? $obMan->getFio() : $model->manager_id;?></p>
								<p> <small><?php echo is_object($obMan = $model->manager) ? $obMan->getRoleStr() : 'N/A';?></small>
								</p>
							</div>
						</div>
					</section>
					<section>
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
							<?php if(!empty($arContacts)):?>

							<?php
								$arItem = [];
								foreach($arContacts as $contact)
								{
									$arItem [] = [
										'label' => $contact->fio.'('.$contact->post.')',
										'content'=>$this->render('_part_collapse_contact',['contact' => $contact])
									];
								}
							?>
							<?php echo CollapseWidget::widget([
									'items' => $arItem
								]);
							?>

							<?php else:?>
								<?=Yii::t('app/crm','No contacts');?>
							<?php endif;?>
							<br />
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
										echo $this->render('_part_form_file',['model' => $obFile]);
										Modal::end();
									?>
								</li>
							</ul>
							<div class="clearfix"></div>
						</div>
						<div class="panel-body">
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
