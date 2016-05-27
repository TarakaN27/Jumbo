<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\ExchangeRates */
/* @var $form yii\widgets\ActiveForm */

$this->registerJs("
function checkStateUseBase()
{
    var
        useBase = $('#exchangerates-use_base').is(':checked'),
        baseID = $('#exchangerates-base_id'),
        factor = $('#exchangerates-factor');

    if(useBase)
    {
        $('#exchangerates-use_exchanger').removeAttr('checked');
        $('#exchangerates-bank_id').attr('disabled','disabled');
        baseID.removeAttr('disabled');
        factor.removeAttr('disabled');
    }else{
        baseID.val('');
        baseID.attr('disabled','disabled');

        if(!$('#exchangerates-use_exchanger').is(':checked'))
        {
            factor.val('');
            factor.attr('disabled','disabled');
        }
    }
}

function checkStateUseExchanger()
{
    var
        bankID = $('#exchangerates-bank_id'),
        factor = $('#exchangerates-factor'),
        useExch = $('#exchangerates-use_exchanger').is(':checked');

    if(useExch)
    {
        $('#exchangerates-use_base').removeAttr('checked');
        $('#exchangerates-base_id').attr('disabled','disabled');
        bankID.removeAttr('disabled');
        factor.removeAttr('disabled');
    }else{
        bankID.val('');
        bankID.attr('disabled','disabled');

        if(!$('#exchangerates-use_base').is(':checked'))
        {
            factor.val('');
            factor.attr('disabled','disabled');
        }
    }

}

",\yii\web\View::POS_END);

$this->registerJs("
checkStateUseBase();
checkStateUseExchanger();
$('#exchangerates-use_base').on('change',checkStateUseBase);
$('#exchangerates-use_exchanger').on('change',checkStateUseExchanger);
",\yii\web\View::POS_READY);
?>

<div class="exchange-rates-form">

    <?php $form = ActiveForm::begin([
        'options' => [
            'class' => 'form-horizontal form-label-left'
        ],
        'fieldConfig' => [
            'template' => '<div class="form-group">{label}<div class="col-md-6 col-sm-6 col-xs-12">{input}</div><ul class="parsley-errors-list" >{error}</ul></div>',
            'labelOptions' => ['class' => 'control-label col-md-3 col-sm-3 col-xs-12'],
        ],
    ]); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'code')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'nbrb')->textInput() ?>

    <?= $form->field($model, 'cbr')->textInput() ?>

    <?= $form->field($model, 'nbrb_rate')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'cbr_rate')->textInput(['maxlength' => true]) ?>
    <div>
        <div class="control-label col-md-3 col-sm-3 col-xs-12">

        </div>
        <?= $form->field($model,'use_rur_for_byr')->checkbox();?>
    </div>

    <div>
        <div class="control-label col-md-3 col-sm-3 col-xs-12">

        </div>
        <?= $form->field($model,'use_base')->checkbox();?>
    </div>
    <?=$form->field($model,'base_id')->dropDownList(
        \common\models\ExchangeRates::getRatesCodes($model->isNewRecord ? NULL : $model->id),
        [
            'prompt' => Yii::t('app/services','Choose currency')
        ]
    )?>

    <div>
        <div class="control-label col-md-3 col-sm-3 col-xs-12">

        </div>
        <?= $form->field($model,'use_exchanger')->checkbox();?>
    </div>

    <?= $form->field($model,'bank_id')?>

    <?= $form->field($model,'factor')?>

    <?= $form->field($model, 'need_upd')->dropDownList(\common\models\ExchangeRates::getYesNo()) ?>

    <?= $form->field($model, 'is_default')->dropDownList(\common\models\ExchangeRates::getYesNo()) ?>

    <?=$form->field($model,'doc_n2w_type')->dropDownList(\common\models\ExchangeRates::getN2WMap())?>

    <div>
        <div class="control-label col-md-3 col-sm-3 col-xs-12">
        </div>
        <?= $form->field($model,'show_at_widget')->checkbox();?>
    </div>

    <div class="form-group">
        <div class = "col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app/services', 'Create') : Yii::t('app/services', 'Update btn'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div></div>

    <?php ActiveForm::end(); ?>

</div>
