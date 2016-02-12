<?php
/**
 *
 */
?>
<div class="col-md-12 product_price">
	<table class="table table-bordered table-time-users-sheet">
		<thead>
			<tr>
				<th>
					<?php echo Yii::t('app/reports','Users')?>
				</th>
				<?php foreach($data['arDays'] as $day):?>
				<th class="date-title text-center calendar_day _calendar_<?=$day['class']?>">
					<?=$day['title'];?>
				</th>
				<?php endforeach;?>
				<th>
					<?php echo Yii::t('app/reports','Total')?>
				</th>
			</tr>
		</thead>
		<tbody>
		<?php foreach($data['arUsersByTask'] as $key => $obUser):?>
		<tr class="ts_bg_task ">
			<th scope="row" class="ts-user-col" data-id="<?=$obUser->id;?>"> <?=$obUser->getFio();?></th>
			<?php
			foreach($data['arDays'] as $keyDate => $day):
				$hour = isset($data['arLogTask'][$key][$keyDate]) ? $data['arLogTask'][$key][$keyDate] : 0;
				$total = isset($data['arTotal'][$key]) ? round($data['arTotal'][$key]/3600,2) : 0;
				?>
				<td class="date-row-task text-center calendar_day _calendar_<?=$day['class']?>">

						<span>
							<?php
							if($hour == 0 && $day['need'] == 0)
								echo NULL;
							else
								echo $hour;
							?>
						</span>
				</td>
			<?php endforeach;?>
			<td>
				<span class="<?php if($total > $data['iTotalNeed'])echo 'ts_green';elseif($total < $data['iTotalNeed'])echo 'ts_red';?>">
					<?php echo $total;?>
				</span>/<?=$data['iTotalNeed']?>
			</td>
		</tr>
		<?php endforeach;?>
		<?php foreach($data['arUsersByWorkDay'] as $key => $obUser):?>
			<tr class="ts_bg_work_day">
				<th scope="row" class="ts-user-col" data-id="<?=$obUser->id;?>"><?=$obUser->getFio();?></th>
				<?php foreach($data['arDays'] as $keyDate => $day):
				$hour = isset($data['arLogWorkDay'][$key][$keyDate]) ? $data['arLogWorkDay'][$key][$keyDate] : 0;
					$total = isset($data['arTotal'][$key]) ? round($data['arTotal'][$key]/3600,2) : 0;
				?>
				<td class="date-row-task text-center calendar_day _calendar_<?=$day['class']?>">

						<span>
							<?php
							if($hour == 0 && $day['need'] == 0)
								echo NULL;
							else
								echo $hour;
							?>
						</span>
				</td>
				<?php endforeach;?>
				<td>
				<span class="<?php if($total > $data['iTotalNeed'])echo 'ts_green';elseif($total < $data['iTotalNeed'])echo 'ts_red';?>">
					<?php echo $total;?>
				</span>/<?=$data['iTotalNeed']?>
				</td>
			</tr>
		<?php endforeach;?>
		<?php foreach($data['arUsersOther'] as $key => $obUser):?>
			<tr>
				<th scope="row" class="ts-user-col"><?=$obUser->getFio();?></th>

			</tr>
		<?php endforeach;?>




		</tbody>
	</table>
</div>
