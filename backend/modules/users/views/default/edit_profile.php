<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 13.07.15
 */
$this->title = Yii::t('app/users','Edit profile');
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
?>
<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2><?=\yii\helpers\Html::encode($this->title);?></h2>
                <section class="pull-right">
                    <?= Html::a(Yii::t('app/users', 'To profile'), ['profile'], ['class' => 'btn btn-warning']) ?>
                </section>
                <div class="clearfix"></div>
            </div>
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

                <?=$form->field($model, 'username')->textInput(['maxlength' => TRUE]) ?>

                <?= $form->field($model, 'lname')->textInput(['maxlength' => TRUE]) ?>
                <?= $form->field($model, 'fname')->textInput(['maxlength' => TRUE]) ?>
                <?= $form->field($model, 'mname')->textInput(['maxlength' => TRUE]) ?>

                <?= $form->field($model, 'email')->textInput(['maxlength' => TRUE]) ?>

                <div class = "form-group">
        <div class = "col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
            <?=Html::submitButton($model->isNewRecord ? Yii::t('app/users', 'Create') : Yii::t('app/users', 'Update'),
                ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary'])?>
            </div>
    </div>

                <?php ActiveForm::end(); ?>
</div>
</div></div></div>
