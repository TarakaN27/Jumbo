<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\jui\DatePicker;
use kartik\select2\Select2;
/* @var $this yii\web\View */
/* @var $model common\models\Payments */
/* @var $form yii\widgets\ActiveForm */
$fieldTpl = '<div>{input}</div><ul class="parsley-errors-list" >{error}</ul>';
$this->registerJsFile('@web/js/wm_app/helpers.js',[
        'depends' => [
            'yii\web\YiiAsset',
            'yii\bootstrap\BootstrapAsset'
        ],
    ]
);
$this->registerJsFile('@web/js/php_functions/strtotime.js',[
    'position' => \yii\web\View::POS_HEAD
]);
$this->registerJs('

function condTypeAction()
{
    var
        condTypes = '.\yii\helpers\Json::encode(\common\models\PaymentCondition::getConditionTypeMap()).'
        condID = $("#payments-condition_id").val();

    if(condTypes[condID] == '.\common\models\PaymentCondition::TYPE_CUSTOM.')
        {
            $("#payments-customprod").removeAttr("disabled");
        }else{
            $("#payments-customprod").val("");
            $("#payments-customprod").attr("disabled","disabled");
        }
}


function findCondition()
{
    var
       iServ = $("#payments-service_id").val(),
       iCuser = $("#payments-cuser_id").val(),
       amount = $("#payments-pay_summ").val(),
       iCurr = $("#payments-currency_id").val(),
       payDate = $("#payments-pay_date").val(),
       iLP = $("#payments-legal_id").val();

    if(iServ == "" || iLP == "" || iCuser == "")
    {
        $("#payments-condition_id").val("");
        return false;
    }

    $.ajax({
        type: "POST",
        cache: false,
        url: "'.\yii\helpers\Url::to(['find-condition']).'",
        dataType: "json",
        data: {iServID:iServ,iContrID:iCuser,lPID:iLP,amount:amount,iCurr:iCurr,payDate:payDate},
        success: function(msg){
                showOptions(msg.visable,"#payments-condition_id");
                if(msg.default != "" && msg.default  != null)
                {
                    $("#payments-condition_id").val(msg.default);
                    boundsCheckingConditions("#"+condID);
                    condTypeAction();
                }
        },
        error: function(msg){
            addErrorNotify("'.Yii::t('app/book','Condition request').'","'.Yii::t('app/book','Server error').'");
            return false;
        }
    });
}

    // Проверка суммы на соотвествие границам условия.
    function boundsCheckingConditions()
    {

        var
            payDate = $("#payments-pay_date").val(),
            iCondID = $("#payments-condition_id").val(),
            iSumm = $("#payments-pay_summ").val(),
            iCurr = $("#payments-currency_id").val();

        if(iCondID == undefined || iCondID == "" || iSumm == undefined || iSumm == "" || iCurr == undefined || iCurr == "" || payDate == undefined || payDate == "" )
            return false;

        $.ajax({
            type: "POST",
            cache: false,
            url: "'.\yii\helpers\Url::to(['/bookkeeping/payment-request/bounds-checking-conditions']).'",
            dataType: "json",
            data: {iCondID:iCondID,iSumm:iSumm,iCurr:iCurr,payDate:strtotime(payDate)},
            success: function(msg){
                if(msg)
                  {
                    addWarningNotify("'.Yii::t('app/book','Bounds checking conditions request').'","'.Yii::t('app/book','Bounds checking conditions FAIL').'");
                  }
            },
            error: function(msg){
                addErrorNotify("'.Yii::t('app/book','Bounds checking conditions request').'","'.Yii::t('app/book','Server error').'");
                return false;
            }
        });
    }

    var
        conditions = '.\yii\helpers\Json::encode(\common\models\PaymentCondition::getConditionWithCurrency(date('Y-m-d',$model->pay_date))).',
        keys = '.\yii\helpers\Json::encode(array_keys(\common\models\PaymentCondition::getConditionMap())).';

    function showOptions(condID,lineID)
    {
        var
            select = $(lineID);
        select.val("");

        showAll = $("#show_all_id").is(":checked");

        select.find("option:not([value=\'\'])").remove();

        $.each(keys, function( index, key ) {
            var
                value = conditions[parseInt(key)];
            if(showAll || $.inArray(parseInt(key),condID) !== -1)
                {
                    select.append("<option value=\'"+key+"\'>"+value+"</option>")
                }
        });
    }

    // действия по клику
    function showAllBtnActions()
    {
        if($(this).is(":checked"))
        {
            showOptions(new Array(),"#payments-condition_id");
        }else{
            findCondition();
        }
    }
    function initDefaultCondition()
    {
        var
            condID = '.\yii\helpers\Json::encode([$model->condition_id]).';
        showOptions(condID,"#payments-condition_id");
        $("#payments-condition_id").val('.$model->condition_id.');
        condTypeAction();
    }
',\yii\web\View::POS_END);

$this->registerJs('
    $("#payments-cuser_id").on("change",findCondition);
     // по дефолту инициализирцем
        initDefaultCondition();
        $("#show_all_id").on("change",showAllBtnActions);
        $("#payments-condition_id").on("change",function(){
            condTypeAction();
        });
        $("#payments-pay_summ").on("change",function(){
            findCondition();
        });

     $("#payments-pay_date").on("change",function(){
        $.ajax({
            type: "POST",
            cache: false,
            url: "'.\yii\helpers\Url::to(['get-conditions']).'",
            dataType: "json",
            data: {date:$(this).val()},
            success: function(msg){
                conditions = msg;
                $.each( $("#payments-condition_id").find("option:not([value=\'\'])"), function( key1, value ) {
                    var
                        key = $(value).attr("value");

                     $(value).html(conditions[key]);
                });
            },
            error: function(msg){
                addErrorNotify("'.Yii::t('app/book','Bounds checking conditions request').'","'.Yii::t('app/book','Server error').'");
                return false;
            }
        });
     });

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
                ->textInput([
                    'maxlength' => true,
                    'onchange' => 'boundsCheckingConditions();'
                ])->label(false)
            ?>
            <?= $form->field($model, 'currency_id',['template' => $fieldTpl,'options' => [
                'class' => 'col-md-4 col-sm-4 col-xs-12',
                'style' => 'padding-right:0px;'
            ]])
                ->dropDownList(\common\models\ExchangeRates::getRatesCodes())->label(false) ?>
        </div>
    </div>
    <?= $form->field($model,'customProd')->textInput()?>

    <?= $form->field($model, 'service_id')->dropDownList(\common\models\Services::getServicesMap(),[
        'prompt' => Yii::t('app/book','BOOK_choose_service'),
        'onchange' => 'findCondition()'
    ]) ?>

    <?= $form->field($model, 'legal_id')->dropDownList(\common\models\LegalPerson::getLegalPersonMap(),[
        'onchange' => 'findCondition();'
    ]) ?>

    <?= $form->field($model, 'condition_id')->dropDownList([],[
        'prompt' => Yii::t('app/book','BOOK_choose_payment_condition'),
        'onchange' => 'boundsCheckingConditions();'
    ]) ?>
    <div class="row">
        <div class="col-md-offset-3 pdd-left-15">
            <?= $form->field($model,"showAll")->checkbox([
                'class' => 'showAllBtn',
                'id' => 'show_all_id'
            ])?>
        </div>
    </div>


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
