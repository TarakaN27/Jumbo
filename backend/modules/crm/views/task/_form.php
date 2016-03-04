<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use vova07\imperavi\Widget as Imperavi;
use common\models\CrmTask;
use yii\web\JsExpression;
/* @var $this yii\web\View */
/* @var $model common\models\CrmTask */
/* @var $form yii\widgets\ActiveForm */

$fieldCheckBoxTmpl = '<div class="form-group">{label}<div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">{input}</div><ul class="parsley-errors-list" >{error}</ul></div>';
$fieldTempl = '<div>{label}{input}</div><ul class="parsley-errors-list" >{error}</ul>';

?>

<div class="crm-task-form">

    <?php $form = ActiveForm::begin([
        'options' => [
            'class' => 'form-horizontal form-label-left',
            'enctype' => 'multipart/form-data'
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

    <?= $form->field($model, 'priority')->dropDownList(
        CrmTask::getPriorityArr(),[
            'prompt' => Yii::t('app/crm','Choose priority')
        ]
    ) ?>

    <div class = "form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="crmtask-time_estimate">
            <?=$model->getAttributeLabel('time_estimate')?>
        </label>
        <div class = "col-md-1 col-sm-1 col-xs-6">
            <?= $form->field($model, 'hourEstimate', ['template' => $fieldTempl,'labelOptions'=>['class' => 'control-label']])->textInput(['maxlength' => TRUE]) ?>
        </div>
        <div class = "col-md-1 col-sm-1 col-xs-6">
            <?= $form->field($model, 'minutesEstimate', ['template' => $fieldTempl,'labelOptions'=>['class' => 'control-label']])->textInput(['maxlength' => TRUE]) ?>
        </div>
    </div>

    <?= $form->field($model, 'deadline')->widget(\kartik\datetime\DateTimePicker::className(),[
        'options' => ['placeholder' => 'Select operating time ...'],
        //'convertFormat' => true,
        'pluginOptions' => [
            'autoclose'=>true,
            'format' => 'd.m.yyyy h:i:s',
            'startDate' => date('d.m.yyyy h:i',time()),
            'todayHighlight' => true
        ]
    ]) ?>

    <?= $form->field($model, 'task_control',['template' => $fieldCheckBoxTmpl])->checkbox() ?>

    <?= $form->field($model, 'assigned_id')->widget(\kartik\select2\Select2::className(),[
        'initValueText' => $sAssName, // set the initial display text
        'options' => [
            'placeholder' => Yii::t('app/crm','Search for a users ...')
        ],
        'pluginOptions' => [
            'allowClear' => true,
            'minimumInputLength' => 2,
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

    <?php
        echo
            $form->field($model, 'arrAcc')->widget(\kartik\select2\Select2::className(),[
                'initValueText' => $sAssName, // set the initial display text
                'data' => $data,
                'options' => [
                    'placeholder' => Yii::t('app/crm','Search for a users ...'),
                    'multiple' => true
                ],
                'pluginOptions' => [
                    'allowClear' => true,
                    'minimumInputLength' => 2,
                    'ajax' => [
                        'url' => \yii\helpers\Url::to(['/ajax-select/get-b-user']),
                        'dataType' => 'json',
                        'data' => new JsExpression('function(params) { return {q:params.term}; }')
                    ],
                    'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                    'templateResult' => new JsExpression('function(cmp_id) { return cmp_id.text; }'),
                    'templateSelection' => new JsExpression('function (cmp_id) { return cmp_id.text; }'),
                ],
            ]);
    ?>

    <?php
    if(!isset($hideCuser) && !isset($hideContact))
        echo $form->field($model, 'cmp_id')->widget(\kartik\select2\Select2::className(),[
            'initValueText' => $cuserDesc, // set the initial display text
            'options' => [
                'placeholder' => Yii::t('app/crm','Search for a company ...')
            ],
            'pluginOptions' => [
                'allowClear' => true,
                'minimumInputLength' => 2,
                'ajax' => [
                    'url' => \yii\helpers\Url::to(['/ajax-select/get-cmp']),
                    'dataType' => 'json',
                    'data' => new JsExpression('function(params) { return {q:params.term}; }')
                ],
                'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                'templateResult' => new JsExpression('function(cmp_id) { return cmp_id.text; }'),
                'templateSelection' => new JsExpression('function (cmp_id) { return cmp_id.text; }'),
            ],
        ])
    ?>
    <?php
    if(!isset($hideCuser)  && !isset($hideContact))
        echo $form->field($model, 'contact_id')->widget(\kartik\select2\Select2::className(),[
        'initValueText' => $contactDesc, // set the initial display text
        'options' => [
            'placeholder' => Yii::t('app/crm','Search for a contact ...')
        ],
        'pluginOptions' => [
            'allowClear' => true,
            'minimumInputLength' => 2,
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

    <?php
        if(isset($hideCuser) && !isset($hideContact))
            echo $form->field($model, 'contact_id')->widget(\kartik\select2\Select2::className(),[
                'initValueText' => $contactDesc, // set the initial display text
                'data' => isset($dataContact) ? $dataContact : [],
                'options' => [
                    'placeholder' => Yii::t('app/crm','Search for a contact ...')
                ],
                /*
                'pluginOptions' => [
                    'allowClear' => true,
                    'minimumInputLength' => 2,
                    'ajax' => [
                        'url' => \yii\helpers\Url::to(['/ajax-select/get-crm-contact']),
                        'dataType' => 'json',
                        'data' => new JsExpression('function(params) { return {q:params.term}; }')
                    ],
                    'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                    'templateResult' => new JsExpression('function(cmp_id) { return cmp_id.text; }'),
                    'templateSelection' => new JsExpression('function (cmp_id) { return cmp_id.text; }'),
                ],
                */
            ]) ?>
    <?php ?>

    <?php
        if($model->isNewRecord)
            echo $form->field($model,'arrFiles')->widget(\common\components\multipleInput\MultipleInput::className(),[
                'limit' => 4,
                'columns' => [
                    [
                        'name'  => 'title',
                        'title' => Yii::t('app/crm','File name'),
                    ],
                    [
                        'name'  => 'fileInput',
                        'enableError' => true,
                        'type' => 'fileInput',
                        'title' => Yii::t('app/crm','Src file'),
                        'options' => [
                            'class' => 'wm_border_none'
                        ]
                    ],

                ]
            ]);
    ?>

    <div class="form-group">
        <div class = "col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app/crm', 'Create') : Yii::t('app/crm', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
