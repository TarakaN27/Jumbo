<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
/* @var $this yii\web\View */
/* @var $model common\models\PartnerWithdrawal */
/* @var $form yii\widgets\ActiveForm */

$this->registerJs("
    function getPartnerPurseAmount()
    {
        var
            pID = $('#partnerwithdrawal-partner_id');

        if(pID == undefined || pID.val() == '')
        {
            return false;
        }

        $.ajax({
            type: \"POST\",
            cache: false,
            url: '".\yii\helpers\Url::to(['partner-purse-amount'])."',
            dataType: \"json\",
            data: {iPID:pID.val()},
            success: function(msg){
                if(!msg)
                  {
                    addWarningNotify('".Yii::t('app/book','Available partner amount request')."','".Yii::t('app/book','Available partner amount FAIL')."');
                  }else{
                        $('#amountAv span').html(msg);
                  }
            },
            error: function(msg){
                addErrorNotify('".Yii::t('app/book','Available partner amount request')."','".Yii::t('app/book','Server error')."');
                return false;
            }
        });
    }
");

$this->registerJs("
$('#partnerwithdrawal-partner_id').on('change',getPartnerPurseAmount);
getPartnerPurseAmount();
",\yii\web\View::POS_READY);
?>

<div class="partner-withdrawal-form">

    <?php $form = ActiveForm::begin([
        'options' => [
            'class' => 'form-horizontal form-label-left',
            //'enctype' => 'multipart/form-data'
        ],
        'fieldConfig' => [
            'template' => '<div class="form-group">{label}<div class="col-md-6 col-sm-6 col-xs-12">{input}</div><ul class="parsley-errors-list" >{error}</ul></div>',
            'labelOptions' => ['class' => 'control-label col-md-3 col-sm-3 col-xs-12'],
        ],
    ]); ?>

    <?= $form->field($model, 'partner_id')->widget(Select2::classname(), [
        'data' => \common\models\Partner::getPartnerMap(),
        'options' => [
            'placeholder' => Yii::t('app/book','BOOK_choose_partner')
        ],
        'pluginOptions' => [
            'allowClear' => true
        ],
    ]); ?>

    <?= $form->field($model, 'amount',[
        'template' => '<div class="form-group">
                {label}
                <div class="col-md-6 col-sm-6 col-xs-12">{input}</div>
                <div class="col-md-3 col-sm-3 col-xs-12" id="amountAv" >'.Yii::t('app/book', 'Available amount').': <span></span></div>
                <ul class="parsley-errors-list" >{error}</ul>
                </div>'
    ])->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'type')->dropDownList(\common\models\PartnerWithdrawal::getTypeArr(),[
        'prompt' => Yii::t('app/book','Choose type')
    ]) ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

    <div class="form-group">
        <div class = "col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app/book', 'Create') : Yii::t('app/book', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
