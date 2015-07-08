<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 02.07.15
 */

use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>
<div class="clearfix"></div>
                    <div class="row">
                        <div class="col-md-12 col-sm-12 col-xs-12">
                            <div class="x_panel">
                                <div class="x_title">
                                    <h2>Изменение пароля</h2>
                                    <section class="pull-right">
                                    <?php echo \yii\helpers\Html::a(Yii::t('app/users','Back'),['view','id'=> $id],['class'=>'btn btn-primary']);?>
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

                                    <?= $form->field($model, 'password')->textInput(['maxlength' => TRUE,]) ?>

                                    <?= $form->field($model, 'repeatPass')->textInput(['maxlength' => TRUE]) ?>

                                    <div class = "form-group">
        <div class = "col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
            <?=Html::submitButton(Yii::t('app/users', 'Update'),
                ['class' => 'btn btn-primary'])?>
    </div>

                                        <?php ActiveForm::end(); ?>
</div>

</div>
