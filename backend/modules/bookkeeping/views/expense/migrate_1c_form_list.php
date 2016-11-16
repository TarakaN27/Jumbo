<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\jui\DatePicker;
use kartik\select2\Select2;
use yii\web\JsExpression;
use common\components\helpers\CustomViewHelper;

/* @var $this yii\web\View */
/* @var $model common\models\Expense */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2><?= Html::encode($this->title) ?></h2>
                <section class="pull-right">
                    <?= Html::a(Yii::t('app/book', 'To list'), ['index'], ['class' => 'btn btn-warning']) ?>
                </section>
                <div class="clearfix"></div>
            </div>
            <div class="expense-form">
                <?php $form = ActiveForm::begin([
                    'options' => [
                        'class' => 'form-horizontal form-label-left'
                    ],
                    'enableClientValidation' => false,
                    'fieldConfig' => [
                        'template' => '<div class="form-group">{label}<div class="col-md-12 col-sm-12 col-xs-12">{input}</div><ul class="parsley-errors-list" >{error}</ul></div>',
                        'labelOptions' => ['class' => 'control-label col-md-4 col-sm-4 col-xs-12'],
                    ],
                ]); ?>
                <table class="table col-md-12">
                    <thead>
                    <th>

                    </th>
                    <th>
                        <?= Yii::t('app/book', 'Cuser ID'); ?>
                    </th>
                    <th>
                        <?= Yii::t('app/book', 'Pay Date'); ?>
                    </th>
                    <th>
                        <?= Yii::t('app/book', 'Pay Summ'); ?>
                    </th>
                    <th>
                        <?= Yii::t('app/book', 'Currency ID'); ?>
                    </th>
                    <th>
                        <?= Yii::t('app/book', 'Description'); ?>
                    </th>
                    <th>
                        <?= Yii::t('app/book', 'Cat ID'); ?>
                    </th>
                    </thead>
                    <tbody>
                    <? foreach ($models as $model) { ?>
                        <? $key = $model->id_1c; ?>
                        <tr>
                            <td><?= $form->field($model, "[{$key}]active")->checkbox(['label' =>""]);?>
                                <?= $form->field($model, "[{$key}]id_1c")->hiddenInput()->label(false);?>
                                <?= $form->field($model, "[{$key}]category1CId")->hiddenInput()->label(false);?>
                            </td>
                            <td>
                                <?if(!$model->cuser_id){?>
                                <?= $form->field($model, "[{$key}]cuser_id")->widget(\kartik\select2\Select2::className(), [
                                    'initValueText' => $cuserDesc, // set the initial display text
                                    'options' => [
                                        'placeholder' => Yii::t('app/crm', 'Search for a company ...')
                                    ],
                                    'pluginOptions' => [
                                        'allowClear' => true,
                                        'minimumInputLength' => 2,
                                        'ajax' => [
                                            'url' => \yii\helpers\Url::to(['/ajax-select/get-expense-user']),
                                            'dataType' => 'json',
                                            'data' => new JsExpression('function(params) { return {q:params.term}; }')
                                        ],
                                        'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                                        'templateResult' => new JsExpression('function(cmp_id) { return cmp_id.text; }'),
                                        'templateSelection' => new JsExpression('function (cmp_id) { return cmp_id.text; }'),
                                    ],
                                ])->label(false) ?>
                                <?}else{?>
                                    <h5><?=$model->cuserName?></h5>
                                    <?= $form->field($model, "[{$key}]cuser_id")->hiddenInput()->label(false);?>
                                <?}?>
                            </td>
                            <td width ="10%">
                                <?= $form->field($model, "[{$key}]pay_date")->widget(DatePicker::className(), [
                                    'dateFormat' => 'dd.MM.yyyy',
                                    'clientOptions' => [
                                        'defaultDate' => date('d.m.Y', time())
                                    ],
                                    'options' => [
                                        'class' => 'form-control'
                                    ]
                                ])->label(false); ?>
                            </td>
                            <td width ="10%">
                                <?= $form->field($model, "[{$key}]pay_summ", ['options' => [
                                ]])->textInput(['maxlength' => true])->label(false) ?>
                            </td>
                            <td width ="7%">
                                <?= $form->field($model, "[{$key}]currency_id", ['options' => [
                                ]])
                                    ->dropDownList(\common\models\ExchangeRates::getRatesCodes())->label(false) ?>
                            </td>
                            <td width="30%">
                                <?= $form->field($model, "[{$key}]description")->textarea(['rows' => 2])->label(false); ?>
                            </td>
                            <td width="15%">
                                <?= $form->field($model, "[{$key}]cat_id")->dropDownList(
                                    \common\models\ExpenseCategories::getExpenseCatMapWithoutParent(), [
                                    'prompt' => Yii::t('app/book', 'BOOK_choose_expense_category')
                                ])->label(false); ?>
                            </td>
                        </tr>
                    <? } ?>
                </table>
                <div class="form-group">
                    <?= Html::submitButton(
                        $model->isNewRecord ? Yii::t('app/book', 'Create') : Yii::t('app/book', 'Update btn'),
                        ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']
                    ) ?>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>





