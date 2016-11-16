<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\BillDocxTemplate */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="bill-docx-template-form">

    <?php $form = ActiveForm::begin([
        'options' => [
            'class' => 'form-horizontal form-label-left',
            'enctype' => 'multipart/form-data'
        ],
        'fieldConfig' => [
            'template' => '<div class="form-group">{label}<div class="col-md-6 col-sm-6 col-xs-12">{input}</div><ul class="parsley-errors-list" >{error}</ul></div>',
            'labelOptions' => ['class' => 'control-label col-md-3 col-sm-3 col-xs-12'],
        ],
    ]); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?php if(!$model->isNewRecord):?>
        <div class="form-group field-billdocxtemplate-src">
            <div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="billdocxtemplate-src"><?=Yii::t('app/documents','Current file')?>:</label>
                <div class="col-md-6 col-sm-6 col-xs-12" style="padding-top: 8px;">
                    <?=Html::a(
                        $model->src,
                        ['download','id' => $model->id],
                        [
                            'target' => '_blank'
                        ]
                        )?>
                </div>
            </div>
        </div>
    <?php endif;?>
    <?= $form->field($model, 'src')->fileInput()?>

    <?= $form->field($model, 'is_default')->dropDownList(\common\models\BillDocxTemplate::getYesNo())?>

    <div class="form-group">
        <div class = "col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app/documents', 'Create') : Yii::t('app/documents', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
