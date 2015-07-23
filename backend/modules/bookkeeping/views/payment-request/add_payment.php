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

    aSummDispl.html(aSumm.val() - tmpSumm);
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

',\yii\web\View::POS_END);
$this->registerJs('
countASumm();
initBehavior();
$(".dynamicform_wrapper").on("afterInsert", function(e, item) {
    initBehavior();
});
$(".dynamicform_wrapper").on("afterDelete", function(e) {
    countASumm();
});
$(document).on("submit", "form#dynamic-form", validateFormLogic);
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
                        <?= Html::button('<i class="glyphicon glyphicon-plus" ></i>'.Yii::t('app/book','Add new payment'),['class' => 'add-item btn btn-success'])?>
                        <?= Html::submitButton(Yii::t('app/book','Save'), ['class' => 'btn btn-primary']) ?>
                        <?= Html::a(Yii::t('app/book', 'To list'), ['index'], ['class' => 'btn btn-warning']) ?>
                    </section>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content container-items">
                    <div class="row">
                        <div class="col-md-6 col-sm-6 col-xs-12">
                             <?php
                                $sCurrn = is_object($obCur = $modelP->currency) ? $obCur->code : 'N/A';
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
                                            \common\models\Services::getServicesMap()) ?>
                                        <?= $form->field($m, "[{$i}]summ")->textInput(['maxlength' => true,'class' => 'form-control psumm']) ?>
                                        <?= $form->field($m, "[{$i}]comment")->textarea() ?>
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