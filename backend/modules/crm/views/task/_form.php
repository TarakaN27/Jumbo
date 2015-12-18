<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use vova07\imperavi\Widget as Imperavi;
use common\models\CrmTask;
use yii\web\JsExpression;
/* @var $this yii\web\View */
/* @var $model common\models\CrmTask */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="crm-task-form">

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

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'description')->widget(Imperavi::className(),[

    ]) ?>

    <?= $form->field($model, 'type')->dropDownList(CrmTask::getTypeArr()) ?>

    <?= $form->field($model, 'priority')->dropDownList(CrmTask::getPriorityArr()) ?>

    <?= $form->field($model, 'time_estimate')->textInput() ?>
    <div>
        <?= $form->field($model, 'hourEstimate')->textInput() ?>
        <?= $form->field($model, 'minutesEstimate')->textInput() ?>
    </div>
    <?= $form->field($model, 'deadline')->widget(\kartik\datetime\DateTimePicker::className(),[
        'options' => ['placeholder' => 'Select operating time ...'],
        'convertFormat' => true,
        'pluginOptions' => [
            'autoclose'=>true,
            'format' => 'yyyy-M-d h:i:s',
            'startDate' => date('d-m-yyyy h:i',time()),
            'todayHighlight' => true
        ]
    ]) ?>

    <?= $form->field($model, 'task_control')->checkbox() ?>

    <?= $form->field($model, 'assigned_id')->widget(\kartik\select2\Select2::className(),[
        'initValueText' => $sAssName, // set the initial display text
        'options' => [
            'placeholder' => Yii::t('app/crm','Search for a users ...')
        ],
        'pluginOptions' => [
            'allowClear' => true,
            'minimumInputLength' => 3,
            'ajax' => [
                'url' => \yii\helpers\Url::to(['/ajax-select/get-b-user']),
                'dataType' => 'json',
                'data' => new JsExpression('function(params) { return {q:params.term}; }')
            ],
            'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
            'templateResult' => new JsExpression('function(cmp_id) { return cmp_id.text; }'),
            'templateSelection' => new JsExpression('function (cmp_id) { return cmp_id.text; }'),
        ],
    ]) ?>

    <?= $form->field($model, 'cmp_id')->widget(\kartik\select2\Select2::className(),[
        'initValueText' => $cuserDesc, // set the initial display text
        'options' => [
            'placeholder' => Yii::t('app/crm','Search for a company ...')
        ],
        'pluginOptions' => [
            'allowClear' => true,
            'minimumInputLength' => 3,
            'ajax' => [
                'url' => \yii\helpers\Url::to(['/ajax-select/get-cmp']),
                'dataType' => 'json',
                'data' => new JsExpression('function(params) { return {q:params.term}; }')
            ],
            'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
            'templateResult' => new JsExpression('function(cmp_id) { return cmp_id.text; }'),
            'templateSelection' => new JsExpression('function (cmp_id) { return cmp_id.text; }'),
        ],
    ]) ?>

    <?= $form->field($model, 'contact_id')->widget(\kartik\select2\Select2::className(),[
        'initValueText' => $contactDesc, // set the initial display text
        'options' => [
            'placeholder' => Yii::t('app/crm','Search for a contact ...')
        ],
        'pluginOptions' => [
            'allowClear' => true,
            'minimumInputLength' => 3,
            'ajax' => [
                'url' => \yii\helpers\Url::to(['/ajax-select/get-crm-contact']),
                'dataType' => 'json',
                'data' => new JsExpression('function(params) { return {q:params.term}; }')
            ],
            'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
            'templateResult' => new JsExpression('function(cmp_id) { return cmp_id.text; }'),
            'templateSelection' => new JsExpression('function (cmp_id) { return cmp_id.text; }'),
        ],
    ]) ?>

    <div class="form-group">
        <div class = "col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app/crm', 'Create') : Yii::t('app/crm', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
