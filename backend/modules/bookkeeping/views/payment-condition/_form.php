<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\components\helpers\CustomViewHelper;
CustomViewHelper::registerJsFileWithDependency('@web/js/accounting/accounting.min.js',$this,[],'accounting');
/* @var $this yii\web\View */
/* @var $model common\models\PaymentCondition */
/* @var $form yii\widgets\ActiveForm */

$fieldTpl = '<div>{input}</div><ul class="parsley-errors-list" >{error}</ul>';
$this->registerJs("
function initFieldsByType(){
    var
        type = $('#paymentcondition-type input:checked').val();

    if(type != ".\common\models\PaymentCondition::TYPE_CUSTOM.")
        {
            $('.usual_type_block').fadeIn(100);
        }else{
            $('.usual_type_block').fadeOut(100);
        }
}
",\yii\web\View::POS_END);
$this->registerJs("
    initFieldsByType();
    $('#paymentcondition-type').on('click','input',initFieldsByType);
    
    $('#paymentcondition-summ_from,#paymentcondition-summ_to,#paymentcondition-corr_factor,#paymentcondition-commission,#paymentcondition-sale,#paymentcondition-tax').on('change',function(){
        amountFormatter(this);
    });
    amountFormatter('#paymentcondition-summ_from');
    amountFormatter('#paymentcondition-summ_to');
    amountFormatter('#paymentcondition-corr_factor');
    
    amountFormatter('#paymentcondition-commission');
    amountFormatter('#paymentcondition-sale');
    amountFormatter('#paymentcondition-tax');
    
",\yii\web\View::POS_READY);
?>

<div class="payment-condition-form">

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

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model,'type')->radioList(\common\models\PaymentCondition::getTypeArr())?>

    <?= $form->field($model, 'service_id')->dropDownList(\common\models\Services::getServicesMap(),[
        'prompt' => Yii::t('app/book','Choose service')
    ]) ?>

    <?= $form->field($model, 'l_person_id')->dropDownList(\common\models\LegalPerson::getLegalPersonMap(),[
        'prompt' => Yii::t('app/book','Choose legal person')
    ]) ?>

    <?= $form->field($model, 'is_resident')->dropDownList(\common\models\PaymentCondition::getYesNo()) ?>

    <div class="form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="payments-service_id">
            <?php echo Html::activeLabel($model,'summ_from');?> -
            <?php echo Html::activeLabel($model,'summ_to');?>
        </label>
        <div class='col-md-6 col-sm-6 col-xs-12'>
            <?= $form->field($model, 'summ_from',['template' => $fieldTpl,'options' => [
                'class' => 'col-md-5 col-sm-5 col-xs-12',
                'style' => 'padding-left:0px;'
            ]])
                ->textInput(['maxlength' => true])->label(false) ?>

            <?= $form->field($model, 'summ_to',['template' => $fieldTpl,'options' => [
                'class' => 'col-md-5 col-sm-5 col-xs-12',
                'style' => 'padding-left:0px;'
            ]])
                ->textInput(['maxlength' => true])->label(false) ?>

            <?= $form->field($model, 'currency_id',['template' => $fieldTpl,'options' => [
                'class' => 'col-md-2 col-sm-2 col-xs-12',
                'style' => 'padding-right:0px;'
            ]])
                ->dropDownList(\common\models\ExchangeRates::getRatesCodes())->label(false) ?>
        </div>
    </div>

    <span class="usual_type_block">
        <?= $form->field($model, 'corr_factor')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'commission')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'sale')->textInput(['maxlength' => true]) ?>
    </span>


    <?= $form->field($model, 'tax')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model,'cond_currency')->dropDownList(\common\models\ExchangeRates::getRatesCodes(),[
        'prompt' => Yii::t('app/book','Choose currency')
    ])?>

    <div class="form-group">
        <div class = "col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
            <?= $form->field($model,'not_use_sale')->checkbox()?>
        </div>
    </div>
    <div class="form-group">
        <div class = "col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
            <?= $form->field($model,'not_use_corr_factor')->checkbox()?>
        </div>
    </div>

    <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

    <div class="form-group">
         <div class = "col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
            <?= Html::submitButton($model->isNewRecord ? Yii::t('app/book', 'Create') : Yii::t('app/book', 'Update btn'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>

         </div>
    </div>
    <?php ActiveForm::end(); ?>

</div>
