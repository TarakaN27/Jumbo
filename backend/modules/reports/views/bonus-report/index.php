<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 29.3.16
 * Time: 11.05
 */
Use yii\helpers\Html;
$this->title = Yii::t('app/bonus','Bonus reports')
?>
<div class="row">
	<div class="col-md-12 col-sm-12 col-xs-12">
		<div class="x_panel">
			<div class="x_title">
				<h2><?= Html::encode($this->title) ?></h2>
				<section class="pull-right">
				</section>
				<div class="clearfix"></div>
			</div>
			<div class="x_content">
				<?php $form = \yii\bootstrap\ActiveForm::begin([
					'options' => [
						// 'class' => 'form-inline'
					],
					// 'fieldConfig' => [
					//     'template' => '<div class="form-group">{label}<div class="col-md-6 col-sm-6 col-xs-12">{input}</div><ul class="parsley-errors-list" >{error}</ul></div>',
					//     'labelOptions' => ['class' => 'control-label col-md-3 col-sm-3 col-xs-12'],
					// ],
				]);?>
				<?php if(Yii::$app->user->can('adminRights')):?>
				<div class="col-md-6 col-sm-6 col-xs-12">

						<?=$form->field($model,'users')->widget(\common\components\multiSelect\MultiSelectWidget::className(),[
							'data' => \backend\models\BUser::getAllMembersMap(),
							'clientOptions' => [

							]
						])?>

				</div>
				<?php endif;?>
				<?php if(Yii::$app->user->can('adminRights')):?>
					<div class="col-md-6 col-sm-6 col-xs-12">
				<?else:?>
					<div class="col-md-12 col-sm-12 col-xs-12">
				<?php endif;?>
					<div class="row">
						<div class="col-md-6 col-sm-6 col-xs-12">
							<?=$form->field($model,'beginDate')->widget(\kartik\date\DatePicker::className(),[
								'options' => [
									'class' => 'form-control'
								],
								'pluginOptions' => [
									'autoclose' => TRUE,
									'format' => 'dd.mm.yyyy',
									'defaultDate' => date('d.m.Y', time())
								]
							])?>
						</div>
						<div class="col-md-6 col-sm-6 col-xs-12">
							<?=$form->field($model,'endDate')->widget(\kartik\date\DatePicker::className(),[
								'options' => [
									'class' => 'form-control'
								],
								'pluginOptions' => [
									'autoclose' => TRUE,
									'format' => 'dd.mm.yyyy',
									'defaultDate' => date('d.m.Y', time())
								]
							])?>
						</div>
					</div>
				</div>


				<div class="col-md-12 col-sm-12 col-xs-12">
					<div class="form-group text-center">
						<?= Html::submitButton(Yii::t('app/reports', 'Get report'), ['class' => 'btn btn-success']) ?>
					</div>
				</div>
				<?php \yii\bootstrap\ActiveForm::end();?>

				<div class="row">
					<?php if(!empty($data) && isset($data['dataProvider'])):?>
						<?=\yii\grid\GridView::widget([
							'dataProvider' => $data['dataProvider'],
							'columns' => [
								[
									'attribute' => 'buser.fio',
									'visible' => Yii::$app->user->can('adminRights')
								],
								[
									'attribute' => 'service.name',
									'label' => Yii::t('app/bonus','Service name')
								],
								'cuser.infoWithSite',
								'payment_id',
								'payment.pay_date:datetime',
								[
									'attribute' => 'scheme.type',
									'value' => function($model){
										$obScheme = $model->scheme;
										return is_object($obScheme) ? $obScheme->getTypeStr() : NULL;
									}
								],
								[
									'attribute' => 'scheme.name',
									'label' => Yii::t('app/bonus','Scheme name'),
									'visible' => Yii::$app->user->can('adminRights')
								],
								'amount:decimal',
							]
						])?>
						<div class="col-md-4 col-md-offset-8">
							<?php if(!empty($data['totalCount'])):?>
								<table class="table table-striped table-bordered">
										<tr>
											<th><?=Yii::t('app/crm','Total');?></th>
											<td><?=Yii::$app->formatter->asDecimal($data['totalCount']);?></td>
										</tr>
								</table>
							<?php endif;?>
						</div>
					<?php endif;?>
				</div>
			</div>
		</div>
	</div>
</div>

