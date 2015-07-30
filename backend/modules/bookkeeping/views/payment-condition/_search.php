<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\search\PaymentConditionSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="payment-condition-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'name') ?>

    <?= $form->field($model, 'description') ?>

    <?= $form->field($model, 'service_id') ?>

    <?= $form->field($model, 'l_person_id') ?>

    <?php // echo $form->field($model, 'is_resident') ?>

    <?php // echo $form->field($model, 'summ_from') ?>

    <?php // echo $form->field($model, 'summ_to') ?>

    <?php // echo $form->field($model, 'corr_factor') ?>

    <?php // echo $form->field($model, 'commission') ?>

    <?php // echo $form->field($model, 'sale') ?>

    <?php // echo $form->field($model, 'tax') ?>

    <?php // echo $form->field($model, 'created_at') ?>

    <?php // echo $form->field($model, 'updated_at') ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app/book', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app/book', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
