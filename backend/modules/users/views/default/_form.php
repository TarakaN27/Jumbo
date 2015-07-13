<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\BUser */
/* @var $form yii\widgets\ActiveForm */
?>



<div class = "x_content">
    <br />
    <?php $form = ActiveForm::begin([
        'options' => [
            'class' => 'form-horizontal form-label-left'
        ],
        'fieldConfig' => [
            'template' => '<div class="form-group">{label}<div class="col-md-6 col-sm-6 col-xs-12">{input}</div><ul class="parsley-errors-list" >{error}</ul></div>',
            'labelOptions' => ['class' => 'control-label col-md-3 col-sm-3 col-xs-12'],
        ],
    ]); ?>

    <?=
    $form->field($model, 'username')->textInput([
        'maxlength' => TRUE,
    ]) ?>

    <?php
    /*
    if($model->isNewRecord) echo $form->field($model, 'password')->textInput(['maxlength' => TRUE]);
    */
    ?>

    <?= $form->field($model, 'lname')->textInput(['maxlength' => TRUE]) ?>
    <?= $form->field($model, 'fname')->textInput(['maxlength' => TRUE]) ?>
    <?= $form->field($model, 'mname')->textInput(['maxlength' => TRUE]) ?>

    <?= $form->field($model, 'email')->textInput(['maxlength' => TRUE]) ?>

    <?= $form->field($model, 'role')->dropDownList(\backend\models\BUser::getRoleArr()) ?>

    <?= $form->field($model, 'status')->dropDownList(\backend\models\BUser::getStatusArr()) ?>
    <div class = "form-group">
        <div class = "col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
            <?=Html::submitButton($model->isNewRecord ? Yii::t('app/users', 'Create') : Yii::t('app/users', 'Update'),
                    ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary'])?>
            </div>
    </div>

    <?php ActiveForm::end(); ?>
</div>

