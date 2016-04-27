<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use wbraganca\dynamicform\DynamicFormWidget;
/* @var $this yii\web\View */
/* @var $model common\models\PartnerExpenseCatLink */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="partner-expense-cat-link-form">

    <?php $form = ActiveForm::begin([
        'options' => [
            'id' => 'dynamic-form',
            //'class' => 'form-horizontal form-label-left',
            //'enctype' => 'multipart/form-data'
        ],
        'fieldConfig' => [
            //'template' => '<div class="form-group">{label}<div class="col-md-6 col-sm-6 col-xs-12">{input}</div><ul class="parsley-errors-list" >{error}</ul></div>',
            //'labelOptions' => ['class' => 'control-label col-md-3 col-sm-3 col-xs-12'],
        ],
    ]); ?>

    <?php DynamicFormWidget::begin([
        'widgetContainer' => 'dynamicform_wrapper', // required: only alphanumeric characters plus "_" [A-Za-z0-9_]
        'widgetBody' => '.container-items', // required: css class selector
        'widgetItem' => '.item', // required: css class
        'limit' => 4, // the maximum times, an element can be cloned (default 999)
        'min' => 1, // 0 or 1 (default 1)
        'insertButton' => '.add-item', // css class
        'deleteButton' => '.remove-item', // css class
        'model' => $models[0],
        'formId' => 'dynamic-form',
        'formFields' => [
            'cuser_id',
            'service_id',
            'connect',
        ],
    ]); ?>

    <div class="container-items"><!-- widgetContainer -->
        <?php foreach ($models as $i => $model): ?>
        <div class="item panel panel-default"><!-- widgetBody -->
            <div class="panel-heading">
                <h3 class="panel-title pull-left"><?=Yii::t('app/users','Partner lead link')?></h3>
                <div class="pull-right">
                    <button type="button" class="add-item btn btn-success btn-xs"><i class="glyphicon glyphicon-plus"></i></button>
                    <button type="button" class="remove-item btn btn-danger btn-xs"><i class="glyphicon glyphicon-minus"></i></button>
                </div>
                <div class="clearfix"></div>
            </div>
            <div class="panel-body">
                <?php
                // necessary for update action.
                if (! $model->isNewRecord) {
                    echo Html::activeHiddenInput($model, "[{$i}]id");
                }
                ?>
                <div class="row">
                    <div class="col-sm-3">
                        <?= $form->field($model, "[{$i}]type")->dropDownList(\common\models\PartnerExpenseCatLink::getTypeMap(),[
                            'prompt' => Yii::t('app/users','Choose type')
                        ]) ?>
                    </div>
                    <div class="col-sm-3">
                        <?= $form->field($model, "[{$i}]legal_person_id")->dropDownList(\common\models\LegalPerson::getLegalPersonMap(),[
                            'prompt' => Yii::t('app/users','Choose legal person')
                        ]) ?>
                    </div>
                    <div class="col-sm-3">
                        <?= $form->field($model, "[{$i}]service_id")->dropDownList(\common\models\Services::getServicesMap(),[
                            'prompt' => Yii::t('app/users','Choose Service')
                        ]) ?>
                    </div>
                    <div class="col-sm-3">
                        <?= $form->field($model, "[{$i}]expanse_cat_id")->dropDownList(\common\models\ExpenseCategories::getExpenseCatMap(),[
                            'prompt' => Yii::t('app/users','Choose expanse category')
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php DynamicFormWidget::end(); ?>

    <div class="form-group">
        <div class = "col-md-6 col-sm-6 col-xs-12">
            <?= Html::submitButton($model->isNewRecord ? Yii::t('app/users', 'Create') : Yii::t('app/users', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
