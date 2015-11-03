<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 3.11.15
 * Time: 11.22
 */
use yii\helpers\Html;
$this->title = Yii::t('app/services', 'Services default contacts');
?>
<div class="row">
	<div class="col-md-12 col-sm-12 col-xs-12">
		<div class="x_panel">
			<div class="x_title">
				<h2><?php echo $this->title;?></h2>
				<section class="pull-right">
					<?= Html::a(Yii::t('app/services', 'To list'), ['index'], ['class' => 'btn btn-warning']) ?>
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
									<th><?=Yii::t('app/services', 'Legal person')?></th>
									<th><?=Yii::t('app/services', 'Contract number')?></th>
									<th><?=Yii::t('app/services', 'Contract date')?></th>
								</tr>
								<?php foreach($legalPerson as $key=>$lp):?>
									<tr>
										<td><?=$lp;?></td>
										<td><?=Html::textInput('number['.$key.']',
												isset($arDC[$key]) ?
													$arDC[$key]->cont_number : NULL,['class' => 'form-control'] )?>
										</td>
										<td style="width: 30%;"><?=\kartik\date\DatePicker::widget([
												'name' => 'date['.$key.']',
												'type' => \kartik\date\DatePicker::TYPE_COMPONENT_PREPEND,
												'value' => isset($arDC[$key]) ?
													$arDC[$key]->cont_date : NULL,
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
							<?= Html::submitButton(Yii::t('app/services', 'save'), ['class' =>'btn btn-success']) ?>
						</div>
					</div>
				</div>
				<?\yii\bootstrap\ActiveForm::end()?>
			</div>
		</div>
	</div>
</div>