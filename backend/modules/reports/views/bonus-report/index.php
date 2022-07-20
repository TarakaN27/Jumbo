<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 29.3.16
 * Time: 11.05
 */
Use yii\helpers\Html;
$this->title = Yii::t('app/bonus','Bonus reports');
if(Yii::$app->user->can('adminRights') || Yii::$app->user->can('bookkeeper')||Yii::$app->user->can('jurist') || Yii::$app->user->can('teamlead_sale'))
    $admin = true;
else $admin= false;
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
						<?=
                        $form->field($model, 'users')->widget(\kartik\select2\Select2::classname(), [
                            'data' => \backend\models\BUser::getAllMembersMap(),
                            'language' => 'de',
                            'options' => ['multiple' => true],
                            'pluginOptions' => [
                                'allowClear' => true
                            ],
                        ])
                        ?>
				</div>

				<?php endif;?>
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <?=
                    $form->field($model, 'cusers')->widget(\kartik\select2\Select2::classname(), [
                        'data' => \common\models\CUser::getContractorMap(),
                        'language' => 'de',
                        'options' => ['multiple' => true],
                        'pluginOptions' => [
                            'allowClear' => true
                        ],
                    ])
                    ?>
                </div>
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
									'defaultDate' => date('d.m.Y', time()),
                                    'weekStart' => '1',
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
									'defaultDate' => date('d.m.Y', time()),
                                    'weekStart' => '1',
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
								[
									'attribute'=>'payment.calculate.profit_for_manager',
									'value' =>function ($model){
										if($model->payment->calculate->profit_for_manager<0){
											if($model->payment->calculate->profit <0)
												return Yii::$app->formatter->asDecimal($model->payment->calculate->profit_for_manager);
											else
												return Yii::$app->formatter->asDecimal(0);
										}else
											return Yii::$app->formatter->asDecimal($model->payment->calculate->profit_for_manager);
									}
								],

								'number_month',
								'bonus_percent:decimal',
								'amount:decimal',
								'is_sale:boolean',
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
				<?if(isset($data['totalProfitByUserType']['managers'])){?>
				<div class="row">
					<h3><?=Yii::t('app/reports', 'Accounters')?></h3>
					<table class="table table-bordered ">
						<thead>
						<tr>
							<th><?=Yii::t('app/reports','User mame');?></th>
							<th><?=Yii::t('app/reports','Sum without sale selected period');?></th>
							<th><?=Yii::t('app/reports','Sum with sale prev month');?></th>
                            <th><?=Yii::t('app/reports','Total bonus');?></th>
						</tr>
						</thead>
						<tbody>
						<?foreach($data['totalProfitByUserType']['managers'] as $key=>$val){?>
						<tr>
							<td>
								<?=$val['fio']?>
							</td>
							<td>
								<?=Yii::$app->formatter->asDecimal(isset($val['sumWithoutNewClientCurrentPeriod'])?$val['sumWithoutNewClientCurrentPeriod']:0 ,2);?>
							</td>
							<td>
								<?=Yii::$app->formatter->asDecimal(isset($val['allSumPrevMonth'])?$val['allSumPrevMonth']:0 ,2);?>
							</td>
                            <td>
                                <?=Yii::$app->formatter->asDecimal(isset($val['totalBonus'])?$val['totalBonus']:0 ,2);?>
                            </td>
						</tr>
						<?}?>
						</tbody>
					</table>
				</div>
				<?}?>
				<?if(isset($data['totalProfitByUserType']['salers'])){?>
					<div class="row">
						<h3><?=Yii::t('app/reports', 'Salers')?></h3>
						<table class="table table-bordered ">
							<thead>
							<tr>
								<th><?=Yii::t('app/reports','User mame');?></th>
								<th><?=Yii::t('app/reports','Sum only sale selected period');?></th>
                                <th><?=Yii::t('app/reports','Total bonus');?></th>
							</tr>
							</thead>
							<tbody>
							<?foreach($data['totalProfitByUserType']['salers'] as $key=>$val){?>
								<tr>
									<td>
										<?=$val['fio']?>
									</td>
									<td>
										<?=Yii::$app->formatter->asDecimal(isset($val['sumOnlySaleCurrentMonth'])?$val['sumOnlySaleCurrentMonth']:0 ,2);?>
									</td>
                                    <td>
                                        <?=Yii::$app->formatter->asDecimal(isset($val['totalBonus'])?$val['totalBonus']:0 ,2);?>
                                    </td>
								</tr>
							<?}?>
							</tbody>
						</table>
					</div>
				<?}?>
				<?if(isset($data['correctCoeff'])&& $data['correctCoeff']){?>
				<h3><?=Yii::t('app/reports', 'Coeffs')?></h3>
				<div class="row">
					<table class="table table-bordered ">
						<thead>
						<tr>
							<th><?=Yii::t('app/reports','User mame');?></th>
							<th><?=Yii::t('app/reports','Month');?></th>
							<th><?=Yii::t('app/reports','Coeff');?></th>
						</tr>
						</thead>
						<tbody>
						<?foreach($data['correctCoeff'] as $userCoeff){?>
							<?foreach($userCoeff as $item){?>
								<tr>
									<td>
										<?=$item->buser->getFio();?>
									</td>
									<td>
										<?=$item->getMonthName();?>
									</td>
									<td>
										<?=Yii::$app->formatter->asDecimal(str_replace(",", ".",$item->coeff));?>
									</td>
								</tr>
							<?}?>
						<?}?>
						</tbody>
					</table>
				</div>
				<?}?>
			</div>
		</div>
	</div>
</div>

