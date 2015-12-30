<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 3.11.15
 * Time: 12.12
 */
use yii\helpers\Html;
$this->title = Yii::t('app/services', 'Services contract');
?>
<div class="row">
	<div class="col-md-12 col-sm-12 col-xs-12">
		<div class="x_panel">
			<div class="x_title">
				<h2><?php echo $this->title;?></h2>
				<section class="pull-right">
					<?= Html::a(Yii::t('app/users', 'To list'), ['/crm/company/index'], ['class' => 'btn btn-warning']) ?>
				</section>
				<div class="clearfix"></div>
			</div>
			<div class = "x_content">
				<?$form = \yii\bootstrap\ActiveForm::begin([]);?>
				<div class="row">
					<div class="form-group">
						<div class="col-md-6 col-sm-6 col-xs-12">
							<table class="table table-bordered">
								<tr>
									<th><?=Yii::t('app/users', 'Service')?></th>
									<th><?=Yii::t('app/users', 'Contract number')?></th>
									<th><?=Yii::t('app/users', 'Contract date')?></th>
								</tr>
								<?php foreach($arService as $key=>$serv):?>
									<tr>
										<td><?=$serv;?></td>
										<td><?=Html::textInput('number['.$key.']',
												isset($arCSC[$key]) ?
													$arCSC[$key]->cont_number : NULL,['class' => 'form-control'] )?>
										</td>
										<td style="width: 30%;"><?=\kartik\date\DatePicker::widget([
												'name' => 'date['.$key.']',
												'type' => \kartik\date\DatePicker::TYPE_COMPONENT_PREPEND,
												'value' => isset($arCSC[$key]) ?
													$arCSC[$key]->cont_date : NULL,
												'pluginOptions' => [
													'autoclose'=>true,
													'format' => 'yyyy-m-dd'
												]
											]);?></td>
									</tr>
								<?php endforeach;?>
							</table>
						</div></div></div>
				<div class="row">
					<div class="form-group">
						<div class="col-md-6 col-sm-6 col-xs-12">
							<?= Html::submitButton(Yii::t('app/users', 'save'), ['class' =>'btn btn-success']) ?>
						</div>
					</div>
				</div>
				<?\yii\bootstrap\ActiveForm::end()?>
			</div>
		</div>
	</div>
</div>
