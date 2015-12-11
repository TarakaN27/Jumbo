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
					<div class="jumbotron">
						<h1>Hello, world!</h1>
						<p>This is a simple hero unit, a simple jumbotron-style component for calling extra attention to featured content or information.</p>
					</div>


				</div>
				<!-- start project-detail sidebar -->
				<div class="col-md-3 col-sm-3 col-xs-12">
					<section>
						<div class="x_title">
							<h2><?php echo Yii::t('app/crm','Assigned At')?></h2>
							<ul class="nav navbar-right panel_toolbox">
								<li><a href="#"><i class="fa fa-pencil"></i> сменить</a>
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
											//	'class' => 'btn btn-sm btn-warning',
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
											//	'class' => 'btn btn-sm btn-warning',
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
								<li>
									<?php foreach($arFile as $file):?>
										<a href=""><i class="fa fa-file-word-o"></i><?=$file->getSplitName();?></a>
									<?php endforeach;?>
								</li>
								<li><a href=""><i class="fa fa-file-pdf-o"></i> UAT.pdf</a>
								</li>
								<li><a href=""><i class="fa fa-mail-forward"></i> Email-from-flatbal.mln</a>
								</li>
								<li><a href=""><i class="fa fa-picture-o"></i> Logo.png</a>
								</li>
								<li><a href=""><i class="fa fa-file-word-o"></i> Contract-10_12_2014.docx</a>
								</li>
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
