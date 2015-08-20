<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Bills */
/* @var $form yii\widgets\ActiveForm */

$this->registerJs("
function fillBillDetail(obD)
{
    $('#bills-use_vat').val(obD.use_vat);
    $('#bills-vat_rate').val(obD.vat_rate);
    $('#bills-description').val(obD.description);
    $('#bills-object_text').val(obD.object_text);
}


function getBillDetail()
{
    var
        iBTpl = $('#bills-bill_template').val();

    if(iBTpl != '')
    {
        fillBillDetail({use_vat:0,vat_rate:'',description:'',object_text:''});
        $.ajax({
        type: 'POST',
        cache: false,
        url: '".\yii\helpers\Url::to(['get-bill-template-detail'])."',
        dataType: 'json',
        data: {iBTpl:iBTpl},
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
        lPerson = $('#bills-l_person_id'),
        service = $('#bills-service_id');

    if(lPerson.val() != '' && service.val() != '' )
    {
        $.ajax({
        type: 'POST',
        cache: false,
        url: '".\yii\helpers\Url::to(['find-bill-template'])."',
        dataType: 'json',
        data: {iServID:service.val(),lPID:lPerson.val()},
        success: function(msg){
            $('#bills-bill_template').removeAttr('disabled');
            if(msg)
              {
                    $('#bills-bill_template').val(msg.id);
                    fillBillDetail(msg);
                    addSuccessNotify('".Yii::t('app/book','Bill template request')."','".Yii::t('app/documents','Template found')."');
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
",\yii\web\View::POS_END);
$this->registerJs("
$('#bills-l_person_id, #bills-service_id').on('change',lPersonAndServiceState);
$('#bills-bill_template').on('change',getBillDetail);
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

    <?
        $options = ['prompt' => Yii::t('app/documents','Choose bill template')];
        if(empty($model->l_person_id) || empty($model->service_id))
            $options['disabled'] = 'disabled';
        echo $form->field($model, 'bill_template')->dropDownList(\common\models\BillTemplate::getBillTemplateMap(),
            $options) ?>

    <?= $form->field($model, 'docx_tmpl_id')->dropDownList(\common\models\BillDocxTemplate::getBillDocxMap()) ?>

    <?= $form->field($model, 'amount')->textInput() ?>

    <?= $form->field($model, 'use_vat')->dropDownList(\common\models\Bills::getYesNo()) ?>

    <?= $form->field($model, 'vat_rate')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'object_text')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'buy_target')->textInput(['maxlength' => true]) ?>


    <div class="form-group">
        <div class = "col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
            <?= Html::submitButton($model->isNewRecord ? Yii::t('app/documents', 'Create') : Yii::t('app/documents', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
