<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 30.10.15
 * Time: 16.35
 */
use yii\helpers\Html;

$this->title = Yii::t('app/documents', 'Act numbers');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class = "row">
	<div class = "col-md-12 col-sm-12 col-xs-12">
		<div class = "x_panel">
			<div class = "x_title">
				<h2><?php echo $this->title?></h2>
				<section class="pull-right">

				</section>
				<div class = "clearfix"></div>
			</div>
			<div class = "x_content">
				<div class="col-xs-12 col-sm-12 col-md-12">
						<div class="col-md-4" >
							<h3><?php echo Yii::t('app/documents','Statistic')?></h3>
							<table class="table ">
								<tr>
									<td>
										<?php echo Yii::t('app/documents','Total numbers')?>
									</td>
									<td>
										1200
									</td>
								</tr>
								<tr>
									<td>
										<?php echo Yii::t('app/documents','Available')?>
									</td>
									<td>
										85
									</td>
								</tr>
							</table>
							</div>
				</div>
				<div class="col-xs-12 col-sm-12 col-md-12">
					<div class="col-xs-12 col-sm-6 col-md-6">
						<h3><?php echo Yii::t('app/documents','Manage')?></h3>
						<hr/>
							<div class="form-horizontal form-label-left">
								<div class="form-group">
									<label class="control-label col-md-3 col-sm-3 col-xs-12">
										<?php echo Yii::t('app/documents','Value')?>
									</label>
									<div class="col-md-6 col-sm-6 col-xs-12">
										<?php echo \yii\helpers\Html::input('text','value',NULL,['class' => 'form-control']);?>
									</div>
								</div>

								<div class="form-group">
									<label class="control-label col-md-3 col-sm-3 col-xs-12">
										<?php echo Yii::t('app/documents','Range')?>
									</label>
									<div class="col-md-6 col-sm-6 col-xs-12">
										<div class="col-md-6 col-sm-6 col-xs-12" style="padding-left:0px;">
											<div>
												<?php echo \yii\helpers\Html::input('text','fromValue',NULL,['class' => 'form-control']);?>
											</div>
										</div>
										<div class="col-md-6 col-sm-6 col-xs-12" style="padding-left:0px; padding-right: 0px;">
											<div>
												<?php echo \yii\helpers\Html::input('text','toValue',NULL,['class' => 'form-control']);?>
											</div>
										</div>
									</div>
								</div>

								<div class="form-group">
									<div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
										<?= Html::submitButton(Yii::t('app/documents', 'Add') , ['class' => 'btn btn-success']) ?>
										<?= Html::submitButton(Yii::t('app/documents', 'Remove') , ['class' => 'btn btn-danger']) ?>
									</div>
								</div>
							</div>
					</div>
					<div class="col-xs-12 col-sm-6 col-md-6">
						<h3><?php echo Yii::t('app/documents','View')?></h3>
						<hr/>
						<div class="form-horizontal form-label-left">
							<div class="form-group">
								<label class="control-label col-md-3 col-sm-3 col-xs-12">
									<?php echo Yii::t('app/documents','Range')?>
								</label>
								<div class="col-md-6 col-sm-6 col-xs-12">
									<div class="col-md-6 col-sm-6 col-xs-12" style="padding-left:0px;">
										<div>
											<?php echo \yii\helpers\Html::input('text','fromValue',NULL,['class' => 'form-control']);?>
										</div>
									</div>
									<div class="col-md-6 col-sm-6 col-xs-12" style="padding-left:0px; padding-right: 0px;">
										<div>
											<?php echo \yii\helpers\Html::input('text','toValue',NULL,['class' => 'form-control']);?>
										</div>
									</div>
								</div>
								<div class="col-md-3 col-sm-3 col-xs-12">
									<?= Html::submitButton(Yii::t('app/documents', 'Show') , ['class' => 'btn btn-warning']) ?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>