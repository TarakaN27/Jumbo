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
$this->registerJsFile('@web/js/wm_app/helpers.js',[
        'depends' => [
            'yii\web\YiiAsset',
            'yii\bootstrap\BootstrapAsset'],
        ]
    );
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
            amount = $("#" + lineID.replace(/-service/gi,"-summ")).val(),
            condID = lineID.replace(/-service/gi,"-condid");

        if(serviceID == "" || amount == " " || amount == undefined || amount == "")
        {
            $("#"+condID).val("");
            $($this).val("");
            addErrorNotify("'.Yii::t('app/book','Error').'","'.Yii::t('app/book','You must set amount and choose service').'")
            return false;
        }

        $.ajax({
            type: "POST",
            cache: false,
            url: "'.\yii\helpers\Url::to(['find-condition']).'",
            dataType: "json",
            data: {iServID:serviceID,iContrID:contrID,lPID:lPID,amount:amount,prID:"'.$modelP->id.'"},
            success: function(msg){
                showOptions(msg.visable,"#"+condID);
                if(msg.default != "" && msg.default  != null)
                {
                    $("#"+condID).val(msg.default);
                    boundsCheckingConditions("#"+condID);
                }


            /*
                if(msg.cID)
                  {
                    $("#"+condID).val(msg.cID);
                    boundsCheckingConditions(parseNum(condID));

                    addSuccessNotify("'.Yii::t('app/book','Condition request').'","'.Yii::t('app/book','Condition found').'");
                  }else{
                    addErrorNotify("'.Yii::t('app/book','Condition request').'","'.Yii::t('app/book','Cant found condition').'");
                    $("#"+condID).val("");
                  }
            */
            },
            error: function(msg){
                addErrorNotify("'.Yii::t('app/book','Condition request').'","'.Yii::t('app/book','Server error').'");
                $("#"+condID).val("");
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
    // Проверка суммы на соотвествие границам условия.
    function boundsCheckingConditions($this)
    {
        if(typeof $this === "number")
        {
            var
                ID = $this;
        }else{
            var
                ID = parseNum($($this).attr("id"));
        }

        if((ID == undefined || ID == "") && ID != 0)
            return false;

        var
            iCondID = $("#addpaymentform-"+ID+"-condid").val(),
            iSumm = $("#addpaymentform-"+ID+"-summ").val();

        if(iCondID == undefined || iCondID == "" || iSumm == undefined || iSumm == "" )
            return false;

        $.ajax({
            type: "POST",
            cache: false,
            url: "'.\yii\helpers\Url::to(['bounds-checking-conditions']).'",
            dataType: "json",
            data: {iCondID:iCondID,iSumm:iSumm,iCurr:"'.$modelP->currency_id.'",payDate:"'.$modelP->pay_date.'"},
            success: function(msg){
                if(msg)
                  {
                    addWarningNotify("'.Yii::t('app/book','Bounds checking conditions request').'","'.Yii::t('app/book','Bounds checking conditions FAIL').'");
                  }else{
                  }
            },
            error: function(msg){
                addErrorNotify("'.Yii::t('app/book','Bounds checking conditions request').'","'.Yii::t('app/book','Server error').'");
                return false;
            }
        });
    }

    var
        conditions = '.\yii\helpers\Json::encode(\common\models\PaymentCondition::getConditionMap()).';

    function showOptions(condID,lineID)
    {
        var
            select = $(lineID);
        select.val("");

        showAll = $(lineID.replace(/-condid/gi,"-showall")).is(":checked");

        select.find("option:not([value=\'\'])").remove();

        $.each(conditions, function( index, value ) {
            if(showAll || $.inArray(parseInt(index),condID) !== -1)
                {
                    select.append("<option value=\'"+index+"\'>"+value+"</option>")
                }
        });
    }

    // по дефолту инициализирцем
    function initDefaultCondition()
    {
        var
            defaultVal = $("#addpaymentform-0-condid").val(),
            condID = '.\yii\helpers\Json::encode($arCondVisible).';
        showOptions(condID,"#addpaymentform-0-condid");
        if(defaultVal != undefined && defaultVal != "" && defaultVal != null )
        {
            $("#addpaymentform-0-condid").val(defaultVal);
        }
    }

    // действия по клику
    function showAllBtnActions()
    {


        if($(this).is(":checked"))
        {
            var
                lineID = $(this).attr("id").replace(/-showall/gi,"-condid");
            showOptions(new Array(),"#"+lineID);
        }else{
            var
                lineID = $(this).attr("id").replace(/-showall/gi,"-service");
            findCondition("#"+lineID);
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
    initDefaultCondition();
    $(".dynamicform_wrapper").on("change",".showAllBtn",showAllBtnActions);
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
                                         [
                                             'attribute' => 'pay_date',
                                             'value' => Yii::$app->formatter->asDate($modelP->pay_date)
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
                                        <?= $form->field($m, "[{$i}]summ")->textInput([
                                            'maxlength' => true,
                                            'class' => 'form-control psumm',
                                            'onchange' => 'boundsCheckingConditions(this);',
                                        ]) ?>
                                        <?= $form->field($m, "[{$i}]comment")->textarea() ?>
                                        <?= $form->field($m, "[{$i}]condID")->dropDownList(\common\models\PaymentCondition::getConditionMap(),[
                                            'prompt' => Yii::t('app/book','Choose condition'),
                                            'data-cond-id' => $i,
                                            'onchange' => 'boundsCheckingConditions(this);',
                                        ]) ?>
                                        <div class="row">
                                            <div class="col-md-offset-2 pdd-left-15">
                                            <?= $form->field($m,"[{$i}]showAll")->checkbox([
                                                'class' => 'showAllBtn'
                                            ])?>
                                                </div>
                                        </div>
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