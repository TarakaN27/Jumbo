<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\CUser */
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

    <?= $form->field($model, 'username')->textInput(['maxlength' => true]) ?>

    <?php if($model->isNewRecord) echo $form->field($model, 'password')->textInput(['maxlength' => true]); ?>

    <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'ext_id')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'manager_id')->dropDownList(\backend\models\BUser::getListManagers(),[
        'prompt' => Yii::t('app/users','Choose_manager')
    ]) ?>

    <?= $form->field($model, 'status')->dropDownList(\common\models\CUser::getStatusArr()) ?>

    <?= $form->field($model, 'type')->dropDownList(\common\models\CUser::getTypeArr()) ?>

    <div class="form-group">
        <div class = "col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app/users', 'Create') : Yii::t('app/users', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        </div>
        </div>

    <?php ActiveForm::end(); ?>

</div>
