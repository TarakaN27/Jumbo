<?php
/**
 *
 */
use common\components\helpers\CustomHelper;
?>
<h5><?=Yii::t('app/reports','Log work type')?>: <?=$data['logWorkTypeStr'];?></h5>
<?php if($data['logWorkTypeID'] == \backend\models\BUser::LOG_WORK_TYPE_TIMER):?>
<div class="col-md-12 product_price">
	<span class="price-tax"><?=Yii::t('app/reports','Reports by work day')?></span></br>
	<table class="table table-bordered">
		<thead>
			<tr>
				<th scope="row"><?=Yii::t('app/reports','Day');?></th>
				<?php foreach($data['arDays'] as $day):?>
					<th class="date-title text-center calendar_day _calendar_<?=$day['class']?>">
						<?=$day['title'];?>
					</th>
				<?php endforeach;?>
			</tr>
		</thead>
		<tbody>
		<tr>
			<th scope="row"><?=Yii::t('app/reports','Work time');?></th>
			<?php foreach($data['arDays'] as $key => $day):?>
				<td
					class="date-row-task text-center calendar_day _calendar_<?=$day['class']?>"
					data-toggle="tooltip"
					data-placement="bottom"
					title="<?=isset($data['arLogWorkDay'][$key]) && isset($data['arWorkDayTooltip'][$data['arLogWorkDay'][$key]->id]) ? $data['arWorkDayTooltip'][$data['arLogWorkDay'][$key]->id] : NULL;?>">
					<?if(isset($data['arLogWorkDay'][$key])):?>
						<?=$data['arLogWorkDay'][$key]->spent_time;?>
					<?endif;?>

				</td>
			<?php endforeach;?>
		</tr>

		<tr>
			<th>
			</th>
			<?php foreach($data['arDays'] as $day):   $total = $day['wdTotal']; ?>
				<td class="date-title calendar_day _calendar_<?=$day['class']?>">
					<span class="<?php if($total > $day['need'])echo 'ts_green';elseif($total < $day['need'])echo 'ts_red';?>"><?=$total;?></span>/<?=$day['need'];?>
				</td>
			<?php endforeach;?>
		</tr>

		</tbody>
	</table>
	<div class="col-md-2 col-md-offset-10">
		<table class="table table-striped">
			<thead>
				<tr>
					<th scope="row"><?=Yii::t('app/reports','Worked');?></th>
					<th scope="row"><?=Yii::t('app/reports','Required');?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td class="text-center <?php if($data['iTotalWorkDayTime']> $data['iTotalNeed'])echo 'ts_green';elseif($data['iTotalWorkDayTime'] < $data['iTotalNeed'])echo 'ts_red';?>"><?=$data['iTotalWorkDayTime'];?></td>
					<td class="text-center"><?=$data['iTotalNeed']?></td>
				</tr>
			</tbody>
		</table>
	</div>
</div>
<?php endif;?>

<div class="col-md-12 product_price">
	<span class="price-tax"><?=Yii::t('app/reports','Reports by task')?></span></br>
<table class="table table-bordered">
	<tr>
		<th>
			Задача
		</th>
		<th>
			Описание
		</th>
        <th>
            Всего
        </th>
		<?php foreach($data['arDays'] as $day):?>
		<th class="date-title text-center calendar_day _calendar_<?=$day['class']?>">
			<?=$day['title'];?>
		</th>
		<?php endforeach;?>
	</tr>
	<?php foreach($data['tasks'] as  $key => $cmp):?>
		<tr>
			<td colspan="2">
				<?php echo $key == 'no_cmp' ? Yii::t('app/reports','Other') : (isset($data['arCmp'][$key]) ? $data['arCmp'][$key] : 'N/A')?>
			</td>
            <td>
                <b>
                <?php
                $cmpSum = 0;
                foreach($cmp as $item){
                    foreach($data['arDays'] as $key2 => $day){
                        $cmpSum += isset($item['log']) && isset($item['log'][$key2]) ? round($item['log'][$key2]/3600,2) : NULL;
                    }
                }
                echo $cmpSum;
                ?>
                </b>
            </td>
			<td colspan="<?=count($data['arDays']);?>">
			</td>
		</tr>
		<?php foreach($cmp as $item):?>
			<tr>
				<td class="text-center"><?=\yii\helpers\Html::a($item['taskID'],['/crm/task/view','id' =>$item['taskID']],['target' => '_blank'])?></td>
				<td><?=\yii\helpers\Html::a(CustomHelper::cuttingString($item['title']),['/crm/task/view','id' =>$item['taskID']],['target' => '_blank'])?></td>
                <td>
                    <b>
                    <?php
                        $taskSum = 0;
                        foreach($data['arDays'] as $key => $day){
                            $taskSum += isset($item['log']) && isset($item['log'][$key]) ? round($item['log'][$key]/3600,2) : NULL;
                        }

                        echo $taskSum;
                    ?>
                    </b>
                </td>
				<?php foreach($data['arDays'] as $key => $day):?>
					<td class="date-row-task text-center calendar_day _calendar_<?=$day['class']?>">
						<?=isset($item['log']) && isset($item['log'][$key]) ? round($item['log'][$key]/3600,2) : NULL?>
					</td>
				<?php endforeach;?>
			</tr>
		<?php endforeach?>
	<?php endforeach;?>
	<?php if($data['logWorkTypeID'] != \backend\models\BUser::LOG_WORK_TYPE_TIMER):?>
	<tr>
		<th colspan="2">
		</th>
        <td>
            <span>
                <?php
                $total = 0;
                foreach($data['arDays'] as $day):
                    $total += round($day['total']/3600,2); ?>
                <?php endforeach;?>
                <?=$total;?>
            </span>
        </td>
		<?php foreach($data['arDays'] as $day):   $total = round($day['total']/3600,2); ?>
			<td class="date-title calendar_day _calendar_<?=$day['class']?>">
				<span class="<?php if($total > $day['need'])echo 'ts_green';elseif($total < $day['need'])echo 'ts_red';?>"><?=$total;?></span>/<?=$day['need'];?>
			</td>
		<?php endforeach;?>
	</tr>
	<?php endif;?>
</table>
<?php if($data['logWorkTypeID'] != \backend\models\BUser::LOG_WORK_TYPE_TIMER):?>
<div class="col-md-2 col-md-offset-10">
<table class="table table-striped">
	<thead>
		<tr>
			<th scope="row"><?=Yii::t('app/reports','Worked');?></th>
			<th scope="row"><?=Yii::t('app/reports','Required');?></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td class="text-center <?php if($data['iTotalLogTime']> $data['iTotalNeed'])echo 'ts_green';elseif($data['iTotalLogTime'] < $data['iTotalNeed'])echo 'ts_red';?>"><?=$data['iTotalLogTime'];?></td>
			<td class="text-center"><?=$data['iTotalNeed']?></td>
		</tr>
	</tbody>
</table>
</div>
</div>
<?php endif;?>
