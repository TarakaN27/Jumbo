<?php
/**
 *
 */
use yii\bootstrap\Modal;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
$this->registerJs("
var
	WORK_DAY = '".Yii::t('app/crm','WORK_DAY')."',
	WORK_DAY_STARTED = '".Yii::t('app/crm','WORK_DAY_STARTED')."',
	WORK_DAY_ENDED = '".Yii::t('app/crm','WORK_DAY_ENDED')."',
	WORK_DAY_ERROR = '".Yii::t('app/crm','WORK_DAY_ERROR')."',
	WORK_DAY_ERROR_END_TIME = '".Yii::t('app/crm','WORK_DAY_ERROR_END_TIME')."',
	WORK_DAY_CONTINUE = '".Yii::t('app/crm','WORK_DAY_CONTINUE')."';

",\yii\web\View::POS_BEGIN);


?>
<div class="work_day_block">
	<div class="clock">
		<div id="Date"></div>

		<ul>
			<li id="hours"> </li>
			<li id="point">:</li>
			<li id="min"> </li>
		</ul>

	</div>
	<?php if($begined):?>

		<?php if(empty($begined->end_time) || $begined->end_time == 0):?>
		<?php
			Modal::begin([
				'header' => '<h2>' . Yii::t('app/crm', 'Work day') . '</h2>',
				'size' => Modal::SIZE_DEFAULT,
				'toggleButton' => [
					'tag' => 'button',
					'class' => 'btn btn-app btn-begin',
					'label' => '<i class="fa fa-stop"></i> ' . Yii::t('app/crm', 'Stop'),
				]
			]);

		?>
		<div>
			<p><?=Yii::t('app/crm','Log date');?> <small><?php echo $model->log_date;?></small></p>
		</div>
		<div>
			<p>Продолжительность дня
				<span data-current="<?=$model->getCurrentSpendTime()?>" id="workedTime">
					<?php echo \common\components\helpers\CustomHelper::getFormatedTaskTime($model->getCurrentSpendTime())?>
				</span>
			</p>
		</div>

		<?php
		$form = ActiveForm::begin([
			'id' => 'work_form_end',
			'action' => ['/ajax-work-day/end']
		]);
		echo Html::activeHiddenInput($model,'log_date');
		echo Html::activeHiddenInput($model,'id');
		echo Html::activeHiddenInput($model,'begin_time');
		?>

		<?=$form->field($model,'end_time')->widget(\kartik\datetime\DateTimePicker::className(),[
			'pluginOptions' => [
				'format' => 'yyyy-mm-dd hh:ii',
				'todayHighlight' => true,
				'autoclose' => true,
			]
		])?>

		<?=$form->field($model,'description')->textarea()?>

		<div class="form-group text-right">
			<?= Html::submitButton(
				Yii::t('app/crm', 'End'),
				['class' =>'btn btn-primary']
			) ?>
		</div>

		<?php
		ActiveForm::end();
		Modal::end();

		$this->registerJs('clock();',\yii\web\View::POS_READY);
		?>

		<?php else:?>
			<?php
			Modal::begin([
				'header' => '<h2>' . Yii::t('app/crm', 'Work day') . '</h2>',
				'size' => Modal::SIZE_DEFAULT,
				'toggleButton' => [
					'tag' => 'button',
					'class' => 'btn btn-app btn-begin',
					'label' => '<i class="fa fa-play"></i> ' . Yii::t('app/crm', 'Continue'),
				]
			]);

			?>
			<div>
				<p><?=Yii::t('app/crm','Log date');?> <small><?php echo $model->log_date;?></small></p>

			</div>
			<div>
				<p>Продолжительность дня
				<span data-current="<?=$model->spent_time;?>" id="workedTime">
					<?php echo \common\components\helpers\CustomHelper::getFormatedTaskTime($model->spent_time)?>
				</span>
				</p>
			</div>
			<?php
			$form = ActiveForm::begin([
				'id' => 'work_form_continue',
				'action' => ['/ajax-work-day/continue']
			]);
			echo Html::activeHiddenInput($model,'log_date');
			echo Html::activeHiddenInput($model,'id');
			echo Html::activeHiddenInput($model,'begin_time');
			?>

			<div class="form-group text-right">
				<?= Html::submitButton(
					Yii::t('app/crm', 'Continue'),
					['class' =>'btn btn-primary']
				) ?>
			</div>

			<?php
			ActiveForm::end();
			Modal::end();
			?>
		<?php endif;?>
	<?php else:?>
		<?php
			Modal::begin([
				'header' => '<h2>' . Yii::t('app/crm', 'Work day') . '</h2>',
				'size' => Modal::SIZE_DEFAULT,
			    'toggleButton' => [
			        'tag' => 'button',
			        'class' => 'btn btn-app btn-begin',
			        'label' => '<i class="fa fa-play"></i> ' . Yii::t('app/crm', 'Begin'),
				]
			]);

		?>
		<div>
			<p><?=Yii::t('app/crm','Log date');?> <small><?php echo $model->log_date;?></small></p>
		</div>
			<?php
				$form = ActiveForm::begin([
					'id' => 'work_form',
					'action' => ['/ajax-work-day/begin']
				]);
		echo Html::activeHiddenInput($model,'log_date');
			?>

			<?=$form->field($model,'begin_time')->widget(\kartik\datetime\DateTimePicker::className(),[
				'pluginOptions' => [
					'format' => 'yyyy-mm-dd hh:ii',
					'todayHighlight' => true,
					'autoclose' => true,
				]
			])?>

			<div class="form-group text-right">
				<?= Html::submitButton(
					Yii::t('app/crm', 'Begin'),
					['class' =>'btn btn-primary']
				) ?>
			</div>

		<?php
			ActiveForm::end();
			Modal::end();
		?>
	<?php endif;?>
</div>
