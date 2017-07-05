<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $model common\models\LegalPerson */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="legal-person-form">

    <?php $form = ActiveForm::begin([
        'options' => [
            'class' => 'form-horizontal form-label-left'
        ],
        'fieldConfig' => [
            'template' => '<div class="form-group">{label}<div class="col-md-6 col-sm-6 col-xs-12">{input}</div><ul class="parsley-errors-list" >{error}</ul></div>',
            'labelOptions' => ['class' => 'control-label col-md-3 col-sm-3 col-xs-12'],
        ],
    ]); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'bank_details')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'bank_details_act')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'legal_person_id')->dropDownList(\common\models\LegalPerson::getLegalPersonMap()); ?>

    <?= $form->field($model, 'bill_hint') ?>

    <?= $form->field($model, 'status')->dropDownList(\common\models\LegalPerson::getStatusArr()) ?>

    <div class="form-group">
        <div class = "col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app/services', 'Create') : Yii::t('app/services', 'Update btn'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div></div>

    <?php ActiveForm::end(); ?>

</div>
