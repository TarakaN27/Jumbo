<?php
use yii\bootstrap\ActiveForm;
use kartik\date\DatePicker;
use kartik\select2\Select2;
use yii\helpers\Html;

$fieldTpl = '<div>{input}</div><ul class="parsley-errors-list" >{error}</ul>';


$this->registerJs("
function checkResident()
{
    var
        sel = $('#paymentrequest-is_unknown').val();

    if(sel == '".\common\models\PaymentRequest::YES."')
        {
            $('.field-paymentrequest-user_name').removeClass('hide');
        }else{
            $('.field-paymentrequest-user_name').addClass('hide');
        }
}

",\yii\web\View::POS_END);


$this->registerJs('
$("#paymentrequest-cntr_id").on("change",function(){
    var
        cID = $(this).val();

   if(cID != "" && cID !=  undefined)
    {
        $.post( "'.\yii\helpers\Url::to(['get-manager']).'", { cID: cID }, function( data ) {
           if(data.mID)
           {
                $("#paymentrequest-manager_id").val(data.mID).change();
           }
           else
           {
                $("#paymentrequest-manager_id").val("").change();
           }
        }, "json")
        .fail(function() {
            addErrorNotify("'.Yii::t('app/common','Error').'","'.Yii::t('app/common','Can not load manager for contractor').'")
        });
    }
});
checkResident();
$(".form-payment-request").on("change","#paymentrequest-is_unknown",checkResident);
',\yii\web\View::POS_READY);

?>
<?php $form = ActiveForm::begin([
    'options' => [
        'class' => 'form-horizontal form-label-left form-payment-request'
    ],
    'fieldConfig' => [
        'template' => '{label}<div class="col-md-6 col-sm-6 col-xs-12">{input}</div><ul class="parsley-errors-list" >{error}</ul>',
        'labelOptions' => ['class' => 'control-label col-md-3 col-sm-3 col-xs-12'],
    ],
]); ?>

<?php  echo $form->field($model, 'cntr_id')->widget(Select2::classname(), [
    'data' => \common\models\CUser::getContractorMap(),
    'options' => ['placeholder' => Yii::t('app/book','BOOK_choose_cuser')],
    'pluginOptions' => [
        'allowClear' => true
    ],
]); ?>

<?php echo $form->field($model,'is_unknown')->dropDownList(\common\models\PaymentRequest::getYesNo());?>
<?php echo $form->field($model,'user_name',['options' => ['class' => 'form-group hide']])->textInput();?>

<?php  echo $form->field($model, 'manager_id')->widget(Select2::classname(), [
    'data' => \backend\models\BUser::getListManagers(),
    'options' => ['placeholder' => Yii::t('app/book','BOOK_choose_managers')],
    'pluginOptions' => [
        'allowClear' => true
    ],
]); ?>


<?= $form->field($model, 'pay_date')->widget(DatePicker::className(),[
    'options' => [
        'class' => 'form-control'
    ],
    'pluginOptions' => [
        'autoclose' => TRUE,
        'format' => 'yyyy-mm-dd',
        'defaultDate' => date('Y-m-d', time())
    ]
]) ?>

<div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="payments-service_id"><?php echo Html::activeLabel($model,'pay_summ');?></label>
                <div class='col-md-6 col-sm-6 col-xs-12'>
                    <?= $form->field($model, 'pay_summ',['template' => $fieldTpl,'options' => [
                        'class' => 'col-md-8 col-sm-8 col-xs-12',
                        'style' => 'padding-left:0px;'
                    ]])
                        ->textInput(['maxlength' => true])->label(false) ?>
                    <?= $form->field($model, 'currency_id',['template' => $fieldTpl,'options' => [
                        'class' => 'col-md-4 col-sm-4 col-xs-12',
                        'style' => 'padding-right:0px;'
                    ]])
                        ->dropDownList(\common\models\ExchangeRates::getRatesCodes())->label(false) ?>
                </div>
            </div>


<?= $form->field($model, 'legal_id')->dropDownList(\common\models\LegalPerson::getLegalPersonMap()) ?>

<?= $form->field($model,'payment_order')->textInput();?>

<?= $form->field($model, 'service_id')->dropDownList(\common\models\Services::getServicesMap(),[
    'prompt' => Yii::t('app/book','Choose service')
])?>


<?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

<div class="form-group">
                <div class = "col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                <?= Html::submitButton($model->isNewRecord ? Yii::t('app/book', 'Create') : Yii::t('app/book', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
                </div>
            </div>

<?php ActiveForm::end(); ?>