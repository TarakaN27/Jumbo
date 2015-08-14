<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 23.07.15
 */
use wbraganca\dynamicform\DynamicFormWidget;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
$this->title  = Yii::t('app/book','Add payment');
$sCurrn = is_object($obCur = $modelP->currency) ? $obCur->code : 'N/A';
$this->registerJs('
function countASumm()
{
    var
        aSumm = $("#aSumm"),
        pSumm = $(".psumm"),
        aSummDispl = $("#aSummDispl"),
        tmpSumm = 0;

    $.each( pSumm, function( key, value ) {
        var
            val = $(value).val();
        if($.isNumeric(val))
            tmpSumm+=parseInt(val);
    });

    $tmp = aSumm.val() - tmpSumm;
    aSummDispl.html((aSumm.val() - tmpSumm)+" '.$sCurrn.'");
    if($tmp < 0)
    {
        aSummDispl.removeClass("green");
        aSummDispl.removeClass("yellow");
        if(!aSummDispl.hasClass("red"))
            aSummDispl.addClass("red");
    }
    if($tmp == 0)
    {
        aSummDispl.removeClass("red");
        aSummDispl.removeClass("yellow");
        if(!aSummDispl.hasClass("green"))
            aSummDispl.addClass("green");
    }
    if($tmp > 0)
    {
        aSummDispl.removeClass("red");
        aSummDispl.removeClass("green");
        if(!aSummDispl.hasClass("yellow"))
            aSummDispl.addClass("yellow");
    }
}
function initBehavior()
{
    $(".psumm").on("change",function(){
        countASumm();
    });
    $(".psumm").on("keyup",function(){
        countASumm();
    });
}

function validateFormLogic()
{
    var
        aSumm = $("#aSumm"),
        pSumm = $(".psumm"),
        tmpSumm = 0;

    $.each( pSumm, function( key, value ) {
        var
            val = $(value).val();
        if($.isNumeric(val))
            tmpSumm+=parseInt(val);
    });

    if(aSumm.val() != tmpSumm)
    {
         addErrorNotify("'.Yii::t('app/book','Error').'","'.Yii::t('app/book','You have to spend all amout').'");
         return false;
    }
    return true;
}

function findCondition($this){
    var
        serviceID = $($this).val(),
        lineID = $($this).attr("id"),
        lPID = "'.$modelP->legal_id.'"
        contrID = "'.$modelP->cntr_id.'",
        condID = lineID.replace(/-service/gi,"-condid");

    if(serviceID == "")
    {
        $("#"+condID).val("");
        return false;
    }

    $.ajax({
        type: "POST",
        cache: false,
        url: "'.\yii\helpers\Url::to(['find-condition']).'",
        dataType: "json",
        data: {iServID:serviceID,iContrID:contrID,lPID:lPID},
        success: function(msg){
            if(msg.cID)
              {
                $("#"+condID).val(msg.cID);
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
function initPayment()
{
    var
        aSumm = $("#aSumm"),
        count = 0,
        pSumm = $(".psumm");


    $.each( pSumm, function( key, value ) {
        count++;
    });

    if(count == 1)
    {
        pSumm.val(aSumm.val());
        countASumm();
    }else{
        if(pSumm.val() == aSumm.val())
        {
            pSumm.val("");
            countASumm();
        }
    }
}
',\yii\web\View::POS_END);
$this->registerJs('
    countASumm();
    initBehavior();
    $(".dynamicform_wrapper").on("afterInsert", function(e, item) {
        initBehavior();
        initPayment();
    });
    $(".dynamicform_wrapper").on("afterDelete", function(e) {
        countASumm();
    });
    $(document).on("submit", "form#dynamic-form", validateFormLogic);
    initPayment();
',\yii\web\View::POS_READY);
?>
<div class="payments-form">
    <?php $form = ActiveForm::begin([
        'id' => 'dynamic-form',
        'options' => [
            'class' => 'form-horizontal form-label-left'
        ],
        'fieldConfig' => [
            'template' => '{label}<div class="col-md-10 col-sm-10 col-xs-12">{input}</div><ul class="parsley-errors-list" >{error}</ul>',
            'labelOptions' => ['class' => 'control-label col-md-2 col-sm-2 col-xs-12'],
        ],
    ]); ?>
    <?php DynamicFormWidget::begin([
        'widgetContainer' => 'dynamicform_wrapper', // required: only alphanumeric characters plus "_" [A-Za-z0-9_]
        'widgetBody' => '.container-items', // required: css class selector
        'widgetItem' => '.item', // required: css class
        'limit' => 10, // the maximum times, an element can be cloned (default 999)
        'min' => 1, // 0 or 1 (default 1)
        'insertButton' => '.add-item', // css class
        'deleteButton' => '.remove-item', // css class
        'model' => $model[0],
        'formId' => 'dynamic-form',
        'formFields' => [
            'service',
            'summ',
            'comment'
        ],
    ]); ?>
    <div class="row">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2><?= Html::encode($this->title) ?></h2>
                    <section class="pull-right">
                        <?= Html::button('<i class="glyphicon glyphicon-plus" ></i> '.Yii::t('app/book','Add new payment'),['class' => 'add-item btn btn-success'])?>
                        <?= Html::submitButton(Yii::t('app/book','Save'), ['class' => 'btn btn-primary']) ?>
                        <?= Html::a(Yii::t('app/book', 'To list'), ['index'], ['class' => 'btn btn-warning']) ?>
                    </section>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content container-items">
                    <div class="row">
                        <div class="col-md-6 col-sm-6 col-xs-12">
                             <?php
                                echo \yii\widgets\DetailView::widget([
                                     'model' => $modelP,
                                     'options' => [
                                         'class' => 'table table-bordered'
                                     ],
                                     'attributes' => [
                                         [
                                             'attribute' => 'cntr_id',
                                             'value' => is_object($obCuser = $modelP->cuser) ? $obCuser->getInfo() : 'N/A'
                                         ],
                                         [
                                             'attribute' => 'legal_id',
                                             'value' => is_object($obLegal = $modelP->legal) ? $obLegal->name : 'N/A'
                                         ],
                                         [
                                              'attribute' => 'pay_summ',
                                              'value' => $modelP->pay_summ.' '.$sCurrn
                                         ],

                                     ]
                                ])?>
                        </div>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <?php echo Html::hiddenInput('availableSumm',$modelP->pay_summ,['id' => 'aSumm'])?>
                            <h3><?php echo Yii::t('app/book','AvailableSumm');?>: <small id="aSummDispl"><?php echo $modelP->pay_summ; ?></small></h3>
                        </div>
                    </div>
                    <?php foreach ($model as $i => $m): ?>
                        <div class="item col-md-6 col-sm-6 col-xs-12">
                            <div class="x_panel">
                                <div class="x_title">
                                    <h2><i class="fa fa-align-left"></i> <?php echo Yii::t('app/book','Payment');?></h2>
                                    <ul class="nav navbar-right">
                                        <li>
                                            <a class="remove-item"><i class="glyphicon glyphicon-remove" style="color:red;cursor: pointer;"></i></a>
                                        </li>
                                    </ul>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="x_content">
                                    <div class="panel-body">
                                        <?= $form->field($m, "[{$i}]service")->dropDownList(
                                            \common\models\Services::getServicesMap(),[
                                            'prompt' => Yii::t('app/book','Choose service'),
                                            'onchange' => 'findCondition(this);',
                                            'data-service-id' => $i
                                        ]) ?>
                                        <?= $form->field($m, "[{$i}]summ")->textInput(['maxlength' => true,'class' => 'form-control psumm']) ?>
                                        <?= $form->field($m, "[{$i}]comment")->textarea() ?>
                                        <?= $form->field($m, "[{$i}]condID")->dropDownList(\common\models\PaymentCondition::getConditionMap(),[
                                            'prompt' => Yii::t('app/book','Choose condition'),
                                            'data-cond-id' => $i
                                        ]) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <?php DynamicFormWidget::end(); ?>
    <?php ActiveForm::end(); ?>
</div>