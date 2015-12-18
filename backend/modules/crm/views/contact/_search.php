<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\search\CrmCmpContactsSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="crm-cmp-contacts-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'cmp_id') ?>

    <?= $form->field($model, 'fio') ?>

    <?= $form->field($model, 'type') ?>

    <?= $form->field($model, 'post') ?>

    <?php // echo $form->field($model, 'description') ?>

    <?php // echo $form->field($model, 'addition_info') ?>

    <?php // echo $form->field($model, 'assigned_at') ?>

    <?php // echo $form->field($model, 'created_at') ?>

    <?php // echo $form->field($model, 'updated_at') ?>

    <?php // echo $form->field($model, 'phone') ?>

    <?php // echo $form->field($model, 'email') ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app/crm', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app/crm', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
