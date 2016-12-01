<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 11.12.15
 * Time: 18.09
 */

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\web\JsExpression;
	$form = ActiveForm::begin([
		'options' => [
			'class' => 'text-left',
			'enctype' => 'multipart/form-data'
		],
	]);
	?>
	<div class="form-group field-crmtask-priority required">
		<div class="form-group">
			<div class="col-md-offset-2 col-md-8 col-sm-8 col-xs-12">
				<?echo \kato\DropZone::widget([
					'id'=> 'dropzoneModal',
					'dropzoneContainer' => 'dropzoneModal',
					'previewsContainer' => 'dropzoneModalpreview',
					'uploadUrl'=>\yii\helpers\Url::to(['/crm/task/upload-file/']),
					'options'=>
						['addRemoveLinks'=> 'true',
							'removedfile' => new JsExpression("function(file) {
                                    var name = file.name;        
                                    $.ajax({
                                        type: 'POST',
                                        url: '/service/crm/task/file-delete',
                                        data: 'id='+file.xhr.response,
                                        dataType: 'html'
                                    });
                                var _ref;
                                return (_ref = file.previewElement) != null ? _ref.parentNode.removeChild(file.previewElement) : void 0;        
                                }"),
							'thumbnailWidth'=> 90,
							'thumbnailHeight'=> 90,
							'dictDefaultMessage' => Yii::t('app/crm', 'Drop file'),
							'dictCancelUpload' => Yii::t('app/crm', 'Cancel upload'),
							'dictRemoveFile'=>Yii::t('app/crm', 'Remove file'),
						],
					'clientEvents'=>[
						'complete' => "function(file){
                                $('#dropzoneModal').append(\"<input type='hidden' name='dropZoneFiles[]' value='\"+file.xhr.response+\"'>\");                           
                            }",
					]
				]);?>
			</div>
		</div>
	</div>
	<div class="row">

		<div class="col-md-6">
			<div class="form-group text-right">
				<?= Html::submitButton(
					$model->isNewRecord ? Yii::t('app/documents', 'Create') : Yii::t('app/documents', 'Update'),
					['class' => $model->isNewRecord ? 'btn btn-success btnContact' : 'btn btn-primary btnContact']
				) ?>
			</div>
		</div>
	</div>

<?php
ActiveForm::end();
