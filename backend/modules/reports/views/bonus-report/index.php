<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 29.3.16
 * Time: 11.05
 */
Use yii\helpers\Html;
$this->title = Yii::t('app/bonus','Bonus reports');
$admin = Yii::$app->user->can('adminRights');
$rowNum = $admin ? 6 : 4;
$rowContNum = $admin ? 6 : 12;
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
				<?php if($admin):?>
				<div class="col-md-6 col-sm-6 col-xs-12">
						<?=$form->field($model,'users')->widget(\common\components\multiSelect\MultiSelectWidget::className(),[
							'data' => \backend\models\BUser::getAllMembersMap(),
						])?>
				</div>
				<?php endif;?>

				<div class="col-md-<?=$rowContNum;?> col-sm-<?=$rowContNum;?> col-xs-12">
					<div class="row">
						<div class="col-md-<?=$rowNum;?> col-sm-<?=$rowNum;?> col-xs-12">
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
						<div class="col-md-<?=$rowNum;?> col-sm-<?=$rowNum;?> col-xs-12">
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
						<div class="col-md-<?=$rowNum;?> col-sm-<?=$rowNum;?> col-xs-12">
							<?=$form->field($model,'bonusType')->dropDownList(
								\common\models\BonusScheme::getBonusSchemeTypeMapByRights(),
								['prompt' => Yii::t('app/bonus','Choose bonus type')])?>
						</div>
						<div class="col-md-<?=$rowNum;?> col-sm-<?=$rowNum;?> col-xs-12">
							<?=$form->field($model,'scheme')->dropDownList(
								\common\models\BonusScheme::getBonusSchemeByRights(),[
								'prompt' => Yii::t('app/bonus','Choose bonus scheme')
							])?>
						</div>
						<div class="col-md-<?=$rowNum;?> col-sm-<?=$rowNum;?> col-xs-12">
							<?=$form->field($model,'service')->dropDownList(\common\models\Services::getServicesMap(),[
								'prompt' => Yii::t('app/bonus','Choose service')
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
								'cuser.infoWithSite',
								[
									'attribute' => 'buser.fio',
									'visible' => $admin
								],
								[
									'attribute' => 'service.name',
									'label' => Yii::t('app/bonus','Service name')
								],
								[
									'attribute' => 'payment_id',
									'format' => 'raw',
									'value' => function($model){
										return Html::a(
											$model->payment_id,
											['/bookkeeping/default/view','id' => $model->payment_id],
											[
												'target' => '_blank'
											]
										);
									}
								],
								'payment.pay_summ:decimal',
								[
									'attribute' => 'payment.currency.code',
									'label' => Yii::t('app/bonus','Code payment currency')
								],

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
									'visible' => $admin
								],
								'amount:decimal',
								'payment.calculate.profit_for_manager:decimal',
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
				<?if(isset($data['calcProfit'])){?>
				<div class="row">
					<h3></h3>
					<table class="table table-bordered ">
						<thead>
						<tr>
							<th><?=Yii::t('app/reports','Sum without sale selected period');?></th>
							<th><?=Yii::t('app/reports','Sum with sale prev month');?></th>
							<th><?=Yii::t('app/reports','Sum only sale selected period');?></th>
							<th><?=Yii::t('app/reports','Sum only sale prev month');?></th>
						</tr>
						</thead>
						<tbody>
						<tr>
							<td>
								<?=Yii::$app->formatter->asDecimal($data['calcProfit']['sumWithoutSaleSelectedPeriod'])?>          </td>
							<td>
								<?=Yii::$app->formatter->asDecimal($data['calcProfit']['sumWithSalePrevMonth'])?>           </td>
							<td>
								<?=Yii::$app->formatter->asDecimal($data['calcProfit']['sumOnlySaleSelectedPeriod'])?>            </td>
							<td>
								<?=Yii::$app->formatter->asDecimal($data['calcProfit']['sumOnlySalePrevMonth'])?>            </td>
						</tr>
						</tbody>
					</table>
				</div>
				<?}?>

				<?php if(isset($data['bonusPaymentRecords'],$data['bonusPaymentRecords']['dataProvider']) && !empty($data['bonusPaymentRecords']['dataProvider'])):
					$arDiffs = isset($data['bonusPaymentRecords']['diffs']) ? $data['bonusPaymentRecords']['diffs'] : [];
					?>
					<?=Html::tag('h3',Yii::t('app/bonus','Payment records bonus'))?>
					<?=\yii\grid\GridView::widget([
						'dataProvider' => $data['bonusPaymentRecords']['dataProvider'],
						'columns' => [
							[
								'attribute' => 'buser.fio',
								'visible' => $admin
							],
							[
								'attribute' => 'record_date',
								'value' => function($model){
									$tmp = $model->record_date;
									return empty($tmp) ? NULL : \common\components\helpers\CustomDateHelper::convertEnToRusMonth(Yii::$app->formatter->asDate($tmp,"MMMM Y"));
								}
							],
							'amount:decimal',
							'is_record:boolean',
							'record_num',
							'bonus.amount:decimal',
							[
								'attribute' => 'bonus.currency_id',
								'value' => 'bonus.currency.code'
							],
							'percents',
							[
								'label' => Yii::t('app/reports','record diffs'),
								'value' => function($model) use ($arDiffs)
								{
									$tmp = isset($arDiffs[$model->id]) ? $arDiffs[$model->id] : NULL;
									return is_null($tmp) ? NULL : round($tmp,2);
								}
							]
						]
					])?>
				<?php endif;?>
			</div>
		</div>
	</div>
</div>

