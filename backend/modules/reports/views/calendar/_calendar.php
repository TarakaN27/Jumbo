<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 9.2.16
 * Time: 16.11
 */
?>
<div class="row">
	<?php foreach($arDays as $days):?>
		<div class="col-6 col-sm-6 col-md-4 text-center calendar-month">
			<h2><?=\common\components\helpers\CustomHelper::my_ucfirst($days['month'])?> <?=$year?></h2>
			<p><?=Yii::t('app/reports',
					'{workDay} working days; {holidays} holidays; {clockRate} clock rate',
					[
						'workDay' => $days['workDay'],
						'holidays' => $days['holiday'],
						'clockRate' => $days['clockRate']
					]);?></p>
			<table class="table table-calendar <?php if($canEdit):?> calendar-edit <?php endif;?>">
				<tbody>
				<?php foreach($days['days'] as $week):?>
					<tr>
						<?php foreach($week as $day):?>
							<?if(!$day['empty']):?>
								<td
									data-work-hour = "<?=$day['workHour']?>"
									data-date="<?=$day['date']?>"
									data-type="<?=$day['type']?>"
									data-num="<?=$day['dayNum']?>"
									data-description="<?=$day['description']?>"
									class="calendar_day _calendar_<?=$day['type']?>"
									data-toggle="tooltip"
									data-placement="bottom"
									title="<?=$day['description']?>"
									>
									<?=$day['dayNum']?>
									<sub><?=$day['workHour']?></sub>
								</td>
							<?php else:?>
								<td>&nbsp;</td>
							<?php endif;?>
						<?endforeach;?>
					</tr>
				<?php endforeach;?>
				</tbody>
			</table>
		</div><!--/span-->
	<?php endforeach;?>
</div>
