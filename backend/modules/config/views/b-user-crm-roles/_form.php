<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use wbraganca\dynamicform\DynamicFormWidget;
use common\models\BUserCrmRules;
/* @var $this yii\web\View */
/* @var $model common\models\BUserCrmRoles */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="buser-crm-roles-form">

    <?php $form = ActiveForm::begin([
        'options' => [
            'class' => 'form-horizontal form-label-left',
            //'enctype' => 'multipart/form-data'
        ],
        'id' => 'dynamic-form'
//        'fieldConfig' => [
//            'template' => '<div class="form-group">{label}<div class="col-md-6 col-sm-6 col-xs-12">{input}</div><ul class="parsley-errors-list" >{error}</ul></div>',
//            'labelOptions' => ['class' => 'control-label col-md-3 col-sm-3 col-xs-12'],
//        ],
    ]); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>


    <div class="panel panel-default">
        <div class="panel-heading">
            <h4>
                <i class="glyphicon glyphicon-envelope"></i> <?=Yii::t('app/crm','Rules'); ?>
            </h4>

        </div>
        <div class="panel-body">
            <?php DynamicFormWidget::begin([
                'widgetContainer' => 'dynamicform_wrapper', // required: only alphanumeric characters plus "_" [A-Za-z0-9_]
                'widgetBody' => '.container-items', // required: css class selector
                'widgetItem' => '.item', // required: css class
                'limit' => count(BUserCrmRules::getEntityArr()), // the maximum times, an element can be cloned (default 999)
                'min' => 1, // 0 or 1 (default 1)
                'insertButton' => '.add-item', // css class
                'deleteButton' => '.remove-item', // css class
                'model' => $modelRule[0],
                'formId' => 'dynamic-form',
                'formFields' => [
                    'entity',
                    'crt',
                    'rd',
                    'upd',
                    'del',
                ],
            ]); ?>
            <button type="button" class="add-item btn btn-success">
                <i class="glyphicon glyphicon-plus"></i> <?=Yii::t('app/crm','Add new rule')?>
            </button>
            <div class="container-items"><!-- widgetContainer -->
                <?php foreach ($modelRule as $i => $rule): ?>
                    <div class="item "><!-- widgetBody -->
                            <?php
                            // necessary for update action.
                            if (! $rule->isNewRecord) {
                                echo Html::activeHiddenInput($rule, "[{$i}]id");
                            }
                            ?>
                            <div class="row">
                                <div class="col-md-4">
                            <?= $form->field($rule, "[{$i}]entity")->dropDownList(BUserCrmRules::getEntityArr()) ?>
                                </div>
                                <div class="col-md-7">
                                    <div class="col-sm-3">
                                        <?= $form->field($rule, "[{$i}]crt")->dropDownList(BUserCrmRules::getRuleArr()) ?>
                                    </div>
                                    <div class="col-sm-3">
                                        <?= $form->field($rule, "[{$i}]rd")->dropDownList(BUserCrmRules::getRuleArr()) ?>
                                    </div>
                                    <div class="col-sm-3">
                                        <?= $form->field($rule, "[{$i}]upd")->dropDownList(BUserCrmRules::getRuleArr()) ?>
                                    </div>
                                    <div class="col-sm-3">
                                        <?= $form->field($rule, "[{$i}]del")->dropDownList(BUserCrmRules::getRuleArr()) ?>
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <button type="button" class="remove-item btn btn-danger btnMinusRule"><i class="glyphicon glyphicon-minus"></i></button>
                                </div>
                            </div><!-- .row -->
                    </div>
                <?php endforeach; ?>
            </div>
            <?php DynamicFormWidget::end(); ?>
        </div>
    </div>


    <div class="form-group">
        <div class = "col-md-6 col-sm-6 col-xs-12">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app/config', 'Create') : Yii::t('app/config', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
