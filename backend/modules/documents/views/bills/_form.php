<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\components\widgets\hideShowBlock\HideShowBlockWidget;

/* @var $this yii\web\View */
/* @var $model common\models\Bills */
/* @var $form yii\widgets\ActiveForm */

$this->registerJs("
function fillBillDetail(obD)
{
    $('#bills-description').val(obD.description);
    $('#bills-object_text').val(obD.object_text);
    $('#bills-offer_contract').val(obD.offer_contract);
}

function getBillDetail()
{
    var
        iCntr = $('#bills-cuser_id').val(),
        iBTpl = $('#bills-bill_template').val();

    if(iBTpl != '')
    {
        fillBillDetail({use_vat:0,vat_rate:'',description:'',object_text:''});
        $.ajax({
        type: 'POST',
        cache: false,
        url: '".\yii\helpers\Url::to(['get-bill-template-detail'])."',
        dataType: 'json',
        data: {iBTpl:iBTpl,iCntr:iCntr},
        success: function(msg){
            if(msg)
              {
                    fillBillDetail(msg);
                    addSuccessNotify('".Yii::t('app/documents','Bill template request')."','".Yii::t('app/documents','Template found')."');
              }else{
                    addErrorNotify('".Yii::t('app/documents','Bill template request')."','".Yii::t('app/documents','Template not found')."');
              }
            },
            error: function(msg){
                addErrorNotify('".Yii::t('app/documents','Bill template request')."','".Yii::t('app/documents','Server error')."');
                return false;
            }
        });

    }else{
        fillBillDetail({use_vat:0,vat_rate:'',description:'',object_text:''});
    }
}


function lPersonAndServiceState()
{
    var
        iCntr = $('#bills-cuser_id').val(),
        lPerson = $('#bills-l_person_id'),
        service = $('#bills-service_id');

    if(lPerson.val() != '' && service.val() != '' )
    {
        $.ajax({
        type: 'POST',
        cache: false,
        url: '".\yii\helpers\Url::to(['find-bill-template'])."',
        dataType: 'json',
        data: {iServID:service.val(),lPID:lPerson.val(),iCntr:iCntr},
        success: function(msg){
            $('#bills-bill_template').removeAttr('disabled');
            if(msg)
              {
                    $('#bills-bill_template').val(msg.id);
                    fillBillDetail(msg);
                    addSuccessNotify('".Yii::t('app/documents','Bill template request')."','".Yii::t('app/documents','Template found')."');
              }else{
                    $('#bills-bill_template').val('');
                    addErrorNotify('".Yii::t('app/documents','Bill template request')."','".Yii::t('app/documents','Template not found')."');
              }
            },
            error: function(msg){
                addErrorNotify('".Yii::t('app/documents','Bill template request')."','".Yii::t('app/documents','Server error')."');
                return false;
            }
        });
    }else{

    }
}

function checkUseVat()
{
    var
        iCntr = $('#bills-cuser_id').val(),
        lP = $('#bills-l_person_id');

    $('#bills-vat_rate').val('');
    if(lP.val() != '')
    {
        $.ajax({
        type: 'POST',
        cache: false,
        url: '".\yii\helpers\Url::to(['find-legal-person'])."',
        dataType: 'json',
        data: {lPID:lP.val(),iCntr:iCntr},
        success: function(msg){
            if(msg.id)
            {
                if(msg.use_vat)
                    {
                        $('#bills-use_vat').val(msg.use_vat);
                        $('#bills-vat_rate').val('".\common\components\helpers\CustomHelper::getVat()."');
                    }
                if(msg.docx_id)
                    $('#bills-docx_tmpl_id').val(msg.docx_id);
                addSuccessNotify('".Yii::t('app/documents','Legal person request')."','".Yii::t('app/documents','Legal person found')."');
            }else{
                $('#bills-use_vat').val('".\common\models\BillTemplate::NO."');
                addSuccessNotify('".Yii::t('app/documents','Legal person request')."','".Yii::t('app/documents','Legal person not found')."');
            }
        },
        error: function(msg){
            addErrorNotify('".Yii::t('app/documents','Legal person request')."','".Yii::t('app/documents','Server error')."');
            return false;
        }
        });
    }
}
",\yii\web\View::POS_END);
$this->registerJs("
    $('#bills-l_person_id, #bills-service_id').on('change',lPersonAndServiceState);
    $('#bills-bill_template').on('change',getBillDetail);
    $('#bills-l_person_id').on('change',checkUseVat);
",\yii\web\View::POS_READY);
?>

<div class="bills-form">

    <?php $form = ActiveForm::begin([
        'options' => [
            'class' => 'form-horizontal form-label-left'
        ],
        'fieldConfig' => [
            'template' => '<div class="form-group">{label}<div class="col-md-6 col-sm-6 col-xs-12">{input}</div><ul class="parsley-errors-list" >{error}</ul></div>',
            'labelOptions' => ['class' => 'control-label col-md-3 col-sm-3 col-xs-12'],
        ],
    ]); ?>

    <?
        if(!Yii::$app->user->can('only_manager'))
            echo $form->field($model, 'manager_id')->widget(\kartik\select2\Select2::className(),[
                'data' => \backend\models\BUser::getListManagers(),
                'options' => [
                    'placeholder' => Yii::t('app/documents','Choose manager')
                ],
                'pluginOptions' => [
                    'allowClear' => true
                ],
            ]) ;
    ?>

    <?= $form->field($model, 'cuser_id')->widget(\kartik\select2\Select2::className(),[
        'data' => \common\models\CUser::getContractorMap(),
        'options' => [
            'placeholder' => Yii::t('app/documents','Choose contractor')
        ],
        'pluginOptions' => [
            'allowClear' => true
        ],
    ]) ?>

    <?= $form->field($model, 'l_person_id')->widget(\kartik\select2\Select2::className(),[
        'data' => \common\models\LegalPerson::getLegalPersonMap(),
        'options' => [
            'placeholder' => Yii::t('app/documents','Choose legal person')
        ],
        'pluginOptions' => [
            'allowClear' => true
        ],
    ]) ?>

    <?php
        echo $form->field($model, 'service_id')->widget(\kartik\select2\Select2::className(),[
            'data' => \common\models\Services::getServicesMap(),
            'options' => [
                'placeholder' => Yii::t('app/documents','Choose service')
            ],
            'pluginOptions' => [
                'allowClear' => true
            ],
        ]) ?>

    <?= $form->field($model, 'amount')->textInput() ?>

    <?$hideBlock = HideShowBlockWidget::begin([
        'btnTmpl' => '
        <div class="form-group">
            <div class = "col-md-6 col-sm-6 col-xs-12 col-md-offset-3" style="text-align: center;">
            {btn}
            </div>
        </div>
        '
    ]);?>
        <?
            $options = ['prompt' => Yii::t('app/documents','Choose bill template')];
            if(empty($model->l_person_id) || empty($model->service_id))
                $options['disabled'] = 'disabled';
            echo $form->field($model, 'bill_template')->dropDownList(\common\models\BillTemplate::getBillTemplateMap(),
                $options) ?>

        <?= $form->field($model, 'docx_tmpl_id')->dropDownList(\common\models\BillDocxTemplate::getBillDocxMap()) ?>

        <?= $form->field($model, 'use_vat')->dropDownList(\common\models\Bills::getYesNo()) ?>

        <?= $form->field($model, 'vat_rate')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

        <?= $form->field($model, 'object_text')->textarea(['rows' => 6]) ?>

        <?= $form->field($model,'offer_contract')->textInput();?>

        <?= $form->field($model, 'buy_target')->textInput(['maxlength' => true]) ?>
    <?HideShowBlockWidget::end();?>

    <div class="form-group">
        <div class = "col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
         <?= $form->errorSummary($model); ?>
            </div>
        <div class = "col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
            <?= Html::submitButton($model->isNewRecord ? Yii::t('app/documents', 'Create') : Yii::t('app/documents', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
</div>