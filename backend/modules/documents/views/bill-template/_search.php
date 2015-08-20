<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\search\BillTemplateSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="bill-template-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'name') ?>

    <?= $form->field($model, 'l_person_id') ?>

    <?= $form->field($model, 'service_id') ?>

    <?= $form->field($model, 'object_text') ?>

    <?php // echo $form->field($model, 'description') ?>

    <?php // echo $form->field($model, 'use_vat') ?>

    <?php // echo $form->field($model, 'vat_rate') ?>

    <?php // echo $form->field($model, 'created_at') ?>

    <?php // echo $form->field($model, 'updated_at') ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app/documents', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app/documents', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
