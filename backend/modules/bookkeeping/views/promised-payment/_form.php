<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use common\models\CUser;
/* @var $this yii\web\View */
/* @var $model common\models\PromisedPayment */
/* @var $form yii\widgets\ActiveForm */

$arContrMap = Yii::$app->user->isManager() ?
    CUser::getContractorMapForManager(Yii::$app->user->id) : CUser::getContractorMap();

$arServices = \common\models\Services::getServiceWithAllowEnrollment();
$arSrv = [];
foreach($arServices as $srv)
{
    $arSrv[$srv->id] = $srv;
}
$this->registerJs("

function serviceAction()
    {
        var
            unit_name = $('#unit_name'),
            services = ".\yii\helpers\Json::encode($arSrv).",
            servID = $('#promisedpayment-service_id').val();

        if(servID != undefined && servID != '' && services[servID] != undefined)
        {
            unit_name.html(services[servID].enroll_unit);
        }else{
            unit_name.html('');
        }
    }
",\yii\web\View::POS_END);
$this->registerJs("
serviceAction();
$('#promisedpayment-service_id').on('change',serviceAction);
",\yii\web\View::POS_READY);


$serviceTemplate = '<div class="form-group">{label}<div class="col-md-6 col-sm-6 col-xs-12">{input}<span id="unit_name"></span></div><ul class="parsley-errors-list" >{error}</ul></div>';
?>



<div class="promised-payment-form">

    <?php $form = ActiveForm::begin([
        'options' => [
            'class' => 'form-horizontal form-label-left'
        ],
        'enableClientValidation' => true,
        'fieldConfig' => [
            'template' => '<div class="form-group">{label}<div class="col-md-6 col-sm-6 col-xs-12">{input}</div><ul class="parsley-errors-list" >{error}</ul></div>',
            'labelOptions' => ['class' => 'control-label col-md-3 col-sm-3 col-xs-12'],
        ],
    ]); ?>

    <?= $form->field($model, 'cuser_id')->widget(Select2::classname(), [
        'data' => $arContrMap,
        'options' => [
            'placeholder' => Yii::t('app/book','BOOK_choose_cuser')
        ],
        'pluginOptions' => [
            'allowClear' => true
        ],
    ]); ?>


    <?= $form->field($model,'service_id')->dropDownList(ArrayHelper::map($arSrv,'id','name'),[
        'prompt' => Yii::t('app/book','BOOK_choose_service')
    ])?>

    <?= $form->field($model, 'amount',['template' => $serviceTemplate])->textInput(['maxlength' => true]) ?>


    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app/book', 'Create') : Yii::t('app/book', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
