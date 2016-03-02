<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\jui\DatePicker;
use kartik\select2\Select2;
use yii\web\JsExpression;
/* @var $this yii\web\View */
/* @var $model common\models\Expense */
/* @var $form yii\widgets\ActiveForm */
$fieldTpl = '<div>{input}</div><ul class="parsley-errors-list" >{error}</ul>';
?>

<div class="expense-form">

    <?php $form = ActiveForm::begin([
        'options' => [
            'class' => 'form-horizontal form-label-left'
        ],
        'enableClientValidation' => false,
        'fieldConfig' => [
            'template' => '<div class="form-group">{label}<div class="col-md-6 col-sm-6 col-xs-12">{input}</div><ul class="parsley-errors-list" >{error}</ul></div>',
            'labelOptions' => ['class' => 'control-label col-md-3 col-sm-3 col-xs-12'],
        ],
    ]); ?>

    <?= $form->field($model, 'cat_id')->dropDownList(
        \common\models\ExpenseCategories::getExpenseCatMap(),[
        'prompt' => Yii::t('app/book','BOOK_choose_expense_category')
    ]) ?>

    <?= $form->field($model, 'cuser_id')->widget(\kartik\select2\Select2::className(),[
        'initValueText' => $cuserDesc, // set the initial display text
        'options' => [
            'placeholder' => Yii::t('app/crm','Search for a company ...')
        ],
        'pluginOptions' => [
            'allowClear' => true,
            'minimumInputLength' => 2,
            'ajax' => [
                'url' => \yii\helpers\Url::to(['/ajax-select/get-contractor']),
                'dataType' => 'json',
                'data' => new JsExpression('function(params) { return {q:params.term}; }')
            ],
            'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
            'templateResult' => new JsExpression('function(cmp_id) { return cmp_id.text; }'),
            'templateSelection' => new JsExpression('function (cmp_id) { return cmp_id.text; }'),
        ],
    ]) ?>

    <div class="form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="payments-service_id">
            <?php echo Html::activeLabel($model,'pay_summ');?>
        </label>
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

    <?= $form->field($model, 'legal_id')->dropDownList(
        \common\models\LegalPerson::getLegalPersonMapWithRoleControl()
    ) ?>

    <?= $form->field($model, 'pay_date')->widget(DatePicker::className(),[
        'dateFormat' => 'dd.MM.yyyy',
        'clientOptions' => [
            'defaultDate' => date('d.m.Y',time())
        ],
        'options' => [
            'class' => 'form-control'
        ]
    ]) ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

    <div class="form-group">
        <?= Html::submitButton(
            $model->isNewRecord ? Yii::t('app/book', 'Create') : Yii::t('app/book', 'Update btn'),
            ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']
        ) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
