<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\PaymentCondition */
/* @var $form yii\widgets\ActiveForm */

$fieldTpl = '<div>{input}</div><ul class="parsley-errors-list" >{error}</ul>';
?>

<div class="payment-condition-form">

    <?php $form = ActiveForm::begin([
        'options' => [
            'class' => 'form-horizontal form-label-left'
        ],
        'enableClientValidation' => false,
        'fieldConfig' => [
            'template' => '<div class="form-group">{label}<div class="col-md-6 col-sm-6 col-xs-12">{input}</div><ul class="parsley-errors-list" >{error}</ul></div>',
            'labelOptions' => ['class' => 'control-label col-md-3 col-sm-3 col-xs-12'],
        ],
    ]); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>



    <?= $form->field($model, 'service_id')->dropDownList(\common\models\Services::getServicesMap(),[
        'prompt' => Yii::t('app/book','Choose service')
    ]) ?>

    <?= $form->field($model, 'l_person_id')->dropDownList(\common\models\LegalPerson::getLegalPersonMap(),[
        'prompt' => Yii::t('app/book','Choose legal person')
    ]) ?>

    <?= $form->field($model, 'is_resident')->dropDownList(\common\models\PaymentCondition::getYesNo()) ?>

    <div class="form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="payments-service_id">
            <?php echo Html::activeLabel($model,'summ_from');?> -
            <?php echo Html::activeLabel($model,'summ_to');?>
        </label>
        <div class='col-md-6 col-sm-6 col-xs-12'>
            <?= $form->field($model, 'summ_from',['template' => $fieldTpl,'options' => [
                'class' => 'col-md-5 col-sm-5 col-xs-12',
                'style' => 'padding-left:0px;'
            ]])
                ->textInput(['maxlength' => true])->label(false) ?>

            <?= $form->field($model, 'summ_to',['template' => $fieldTpl,'options' => [
                'class' => 'col-md-5 col-sm-5 col-xs-12',
                'style' => 'padding-left:0px;'
            ]])
                ->textInput(['maxlength' => true])->label(false) ?>

            <?= $form->field($model, 'currency_id',['template' => $fieldTpl,'options' => [
                'class' => 'col-md-2 col-sm-2 col-xs-12',
                'style' => 'padding-right:0px;'
            ]])
                ->dropDownList(\common\models\ExchangeRates::getRatesCodes())->label(false) ?>
        </div>
    </div>

    <?= $form->field($model, 'corr_factor')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'commission')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'sale')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'tax')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

    <div class="form-group">
         <div class = "col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
            <?= Html::submitButton($model->isNewRecord ? Yii::t('app/book', 'Create') : Yii::t('app/book', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>

         </div>
    </div>
    <?php ActiveForm::end(); ?>

</div>
