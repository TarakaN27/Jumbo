<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 9.2.16
 * Time: 10.05
 */
use yii\helpers\Html;
use yii\bootstrap\Modal;
$this->title = Yii::t('app/crm','Calendar');


if($canEdit)
{
	$this->registerJs("
			function editDate()
			{
				var
					workHour = $(this).attr('data-work-hour'),
					type = $(this).attr('data-type'),
					date = $(this).attr('data-date');

				if(date == undefined || date == null || date == '')
					{
						return false;
					}
				$('#editDate').html(date);
				 $.post(
				        '".\yii\helpers\Url::to(['edit-date'])."',
				        {
				            date: date,
				            type: type,
				            workHour: workHour,
				            year: '".$year."'
				        },
				        function (data) {
				            $('#activity-modal .modal-body').html(data);
				            $('#activity-modal').modal();
				        }
				    );
			}


	",\yii\web\View::POS_END);
	$this->registerJs("
		$('#activity-modal').on('beforeSubmit', 'form#editdateform', function () {
		     var form = $(this);
		     // return false if form still have some validation errors
		     if (form.find('.has-error').length) {
		          return false;
		     }
		     // submit form
		     $.ajax({
		          url: form.attr('action'),
		          type: 'post',
		          data: form.serialize(),
		          success: function (res) {
		            if(res.content != undefined)
		            {
		                $('.x_content').html(res.content);
		            }
		            $('#activity-modal .modal-dialog button.close').click();
		          }
		     });
		     return false;
		});
	",\yii\web\View::POS_READY);
	$this->registerJs("
		$('.x_content').on('click','.calendar_day',editDate);
		$('#activity-modal').on('change','input[name=\"CalendarDays[type]\"]',function(){
			var
				type = $('input[name=\"CalendarDays[type]\"]:checked').val();

			if(type != '".\common\models\CalendarDays::TYPE_HOLIDAY."')
				{
					$('#calendardays-work_hour').removeAttr('disabled');
					$('#calendardays-work_hour').val('".\common\components\calendar\Calendar::DEFAULT_WORK_HOUR."');
				}else{
					$('#calendardays-work_hour').val('');
					$('#calendardays-work_hour').attr('disabled','disabled');
				}
		});
		$('#chouseYear').on('change',function(){
			var
				url = '".\yii\helpers\Url::to(['index'])."?year='+$(this).val();
			$(location).attr('href',url);
		});
	",\yii\web\View::POS_READY);
}
?>
<?php Modal::begin([
	'id' => 'activity-modal',
	'header' => '<h2>'.Yii::t('app/reports','Update date').' <span id="editDate"></span></h2>',
	'size' => Modal::SIZE_DEFAULT,
]);?>

<?php Modal::end();?>
<div class="row">
	<div class="col-md-12 col-sm-12 col-xs-12">
		<div class="x_panel">
			<div class="x_title">
				<h2><?= Html::encode($this->title) ?></h2>
				<section class="pull-right">
					<?php echo Html::dropDownList('years',$year,$arYears,['class' => 'form-control','id' => 'chouseYear'])?>
				</section>
				<div class="clearfix"></div>
			</div>
			<div class="x_content">
				<?=$this->render('_calendar',['arDays' => $arDays,'year' => $year,'canEdit' => $canEdit])?>
			</div>
		</div>
	</div>
</div>


