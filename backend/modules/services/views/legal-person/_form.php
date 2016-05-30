<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

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

    <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'doc_requisites')->textarea(['row' => 6])?>

    <?= $form->field($model, 'ynp')->textInput(['maxlength' => true])?>

    <?= $form->field($model,'address')->textarea(['row' => 6])?>

    <?=$form->field($model,'mailing_address')->textarea(['row' => 6])?>

    <?= $form->field($model, 'doc_site')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'doc_email')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'telephone_number')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'use_vat')->dropDownList(\common\models\LegalPerson::getYesNo())?>

    <?= $form->field($model,'docx_id')->dropDownList(\common\models\BillDocxTemplate::getBillDocxMap())?>

    <?= $form->field($model, 'act_tpl_id')->dropDownList(\common\models\ActsTemplate::getActsTplMap())?>

    <?= $form->field($model, 'status')->dropDownList(\common\models\LegalPerson::getStatusArr()) ?>
    <div class="form-group">
        <div class = "col-md-9 col-sm-9 col-xs-12 col-md-offset-3">
            <?= $form->field($model, 'admin_expense')->checkbox()?>
        </div>
    </div>
    <div class="form-group">
        <div class = "col-md-9 col-sm-9 col-xs-12 col-md-offset-3">
            <?= $form->field($model, 'partner_cntr')->checkbox()?>
        </div>
    </div>
    <div class="form-group">
        <div class = "col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app/services', 'Create') : Yii::t('app/services', 'Update btn'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div></div>

    <?php ActiveForm::end(); ?>

</div>
