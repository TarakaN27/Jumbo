<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\search\BillsSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="bills-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'manager_id') ?>

    <?= $form->field($model, 'cuser_id') ?>

    <?= $form->field($model, 'l_person_id') ?>

    <?= $form->field($model, 'service_id') ?>

    <?php // echo $form->field($model, 'docx_tmpl_id') ?>

    <?php // echo $form->field($model, 'amount') ?>

    <?php // echo $form->field($model, 'bill_number') ?>

    <?php // echo $form->field($model, 'bill_date') ?>

    <?php // echo $form->field($model, 'bill_template') ?>

    <?php // echo $form->field($model, 'use_vat') ?>

    <?php // echo $form->field($model, 'vat_rate') ?>

    <?php // echo $form->field($model, 'description') ?>

    <?php // echo $form->field($model, 'object_text') ?>

    <?php // echo $form->field($model, 'buy_target') ?>

    <?php // echo $form->field($model, 'created_at') ?>

    <?php // echo $form->field($model, 'updated_at') ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app/documents', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app/documents', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
