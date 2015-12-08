<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\EntityFields */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="entity-fields-form">

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

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'alias')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'entity')->dropDownList(\common\models\EntityFields::getEntityArr(),[
        'prompt' => Yii::t('app/config','Choose entity')
    ]) ?>

    <?= $form->field($model, 'type')->dropDownList(\common\models\EntityFields::getTypeArr(),[
        'prompt' => Yii::t('app/config','Choose type')
    ]) ?>

    <?= $form->field($model, 'required')->dropDownList(\common\models\EntityFields::getYesNo(),[
        'prompt' => Yii::t('app/config','Choose "yes" if is required')
    ]) ?>

    <?= $form->field($model, 'validate')->dropDownList(\common\models\EntityFields::getValidArr(),[
        'prompt' => Yii::t('app/config','Validate type')
    ]) ?>

    <div class="form-group">
        <div class = "col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app/config', 'Create') : Yii::t('app/config', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
