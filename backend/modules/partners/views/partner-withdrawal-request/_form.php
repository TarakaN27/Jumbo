<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\jui\DatePicker;
use yii\web\JsExpression;
$fieldTpl = '<div>{input}</div><ul class="parsley-errors-list" >{error}</ul>';
/* @var $this yii\web\View */
/* @var $model common\models\PartnerWithdrawalRequest */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="partner-withdrawal-request-form">

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

    <?= $form->field($model, 'partner_id')->widget(\kartik\select2\Select2::className(),[
        'initValueText' => $partnerDesc, // set the initial display text
        'options' => [
            'placeholder' => Yii::t('app/crm','Search for a company ...')
        ],
        'pluginOptions' => [
            'allowClear' => true,
            'minimumInputLength' => 2,
            'ajax' => [
                'url' => \yii\helpers\Url::to(['/ajax-select/get-partners']),
                'dataType' => 'json',
                'data' => new JsExpression('function(params) { return {q:params.term}; }')
            ],
            'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
            'templateResult' => new JsExpression('function(cmp_id) { return cmp_id.text; }'),
            'templateSelection' => new JsExpression('function (cmp_id) { return cmp_id.text; }'),
        ],
    ]) ?>

    <?= $form->field($model, 'type')->dropDownList(\common\models\PartnerWithdrawalRequest::getTypeMap(),[
        'prompt' => Yii::t('app/users','Choose type')
    ]) ?>

    <div class="form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="payments-service_id"><?php echo Html::activeLabel($model,'amount');?></label>
        <div class='col-md-6 col-sm-6 col-xs-12'>
            <?= $form->field($model, 'amount',['template' => $fieldTpl,'options' => [
                'class' => 'col-md-8 col-sm-8 col-xs-12',
                'style' => 'padding-left:0px;'

            ]])
                ->textInput(['maxlength' => true])->label(false)
            ?>
            <?= $form->field($model, 'currency_id',['template' => $fieldTpl,'options' => [
                'class' => 'col-md-4 col-sm-4 col-xs-12',
                'style' => 'padding-right:0px;'
            ]])
                ->dropDownList(\common\models\ExchangeRates::getRatesCodes())->label(false) ?>
        </div>
    </div>

    <?= $form->field($model, 'date')->widget(DatePicker::className(),[
        'dateFormat' => 'dd.MM.yyyy',
        'clientOptions' => [
            'defaultDate' => date('d.m.Y',time())
        ],
        'options' => [
            'class' => 'form-control'
        ]
    ]) ?>

    <div class="form-group">
        <div class = "col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app/users', 'Create') : Yii::t('app/users', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
