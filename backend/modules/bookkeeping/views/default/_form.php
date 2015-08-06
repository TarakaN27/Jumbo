<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\jui\DatePicker;
use kartik\select2\Select2;
/* @var $this yii\web\View */
/* @var $model common\models\Payments */
/* @var $form yii\widgets\ActiveForm */
$fieldTpl = '<div>{input}</div><ul class="parsley-errors-list" >{error}</ul>';
$this->registerJs('
function findCondition()
{
    var
       iServ = $("#payments-service_id").val(),
       iCuser = $("#payments-cuser_id").val(),
       iLP = $("#payments-legal_id").val();

    if(iServ == "" || iLP == "" || iCuser == "")
    {
        $("#payments-condition_id").val("");
        return false;
    }

    $.ajax({
        type: "POST",
        cache: false,
        url: "'.\yii\helpers\Url::to(['/bookkeeping/payment-request/find-condition']).'",
        dataType: "json",
        data: {iServID:iServ,iContrID:iCuser,lPID:iLP},
        success: function(msg){
            if(msg.cID)
              {
                $("#payments-condition_id").val(msg.cID);
                addSuccessNotify("'.Yii::t('app/book','Condition request').'","'.Yii::t('app/book','Condition found').'");
              }else{
                addErrorNotify("'.Yii::t('app/book','Condition request').'","'.Yii::t('app/book','Cant found condition').'");
              }
        },
        error: function(msg){
            addErrorNotify("'.Yii::t('app/book','Condition request').'","'.Yii::t('app/book','Server error').'");
            return false;
        }
    });
}

',\yii\web\View::POS_END);

$this->registerJs('
$("#payments-cuser_id").on("change",findCondition);
',\yii\web\View::POS_READY);

?>

<div class="payments-form">

    <?php $form = ActiveForm::begin([
        'options' => [
            'class' => 'form-horizontal form-label-left'
        ],
        'fieldConfig' => [
            'template' => '{label}<div class="col-md-6 col-sm-6 col-xs-12">{input}</div><ul class="parsley-errors-list" >{error}</ul>',
            'labelOptions' => ['class' => 'control-label col-md-3 col-sm-3 col-xs-12'],
        ],
    ]); ?>

    <?php  echo $form->field($model, 'cuser_id')->widget(Select2::classname(), [
        'data' => \common\models\CUser::getContractorMap(),
        'options' => [
            'placeholder' => Yii::t('app/book','BOOK_choose_cuser')
        ],
        'pluginOptions' => [
            'allowClear' => true
        ],
        ]); ?>

    <?= $form->field($model, 'pay_date')->widget(DatePicker::className(),[
        'dateFormat' => 'dd-MM-yyyy',
        'clientOptions' => [
            'defaultDate' => date('d-m-Y',time())
        ],
        'options' => [
            'class' => 'form-control'
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
    <?= $form->field($model, 'service_id')->dropDownList(\common\models\Services::getServicesMap(),[
        'prompt' => Yii::t('app/book','BOOK_choose_service'),
        'onchange' => 'findCondition()'
    ]) ?>

    <?= $form->field($model, 'legal_id')->dropDownList(\common\models\LegalPerson::getLegalPersonMap(),[
        'onchange' => 'findCondition();'
    ]) ?>

    <?= $form->field($model, 'condition_id')->dropDownList(\common\models\PaymentCondition::getConditionMap(),[
        'prompt' => Yii::t('app/book','BOOK_choose_payment_condition')
    ]) ?>


    <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

    <div class="form-group">
        <div class = "col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
            <?php if(!$model->isNewRecord):?>
                <?=$form->field($model,'updateWithNewCondition')->checkbox();?>
            <?php endif;?>
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app/book', 'Create') : Yii::t('app/book', 'Update btn'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div></div>

    <?php ActiveForm::end(); ?>

</div>
