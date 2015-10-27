<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use common\models\CUser;
/* @var $this yii\web\View */
/* @var $model common\models\PromisedPayment */
/* @var $form yii\widgets\ActiveForm */

$arContrMap = Yii::$app->user->isManager() ?
    CUser::getContractorMapForManager(Yii::$app->user->id) :
    CUser::getContractorMap();
?>

<div class="promised-payment-form">

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

    <?= $form->field($model, 'cuser_id')->widget(Select2::classname(), [
        'data' => $arContrMap,
        'options' => [
            'placeholder' => Yii::t('app/book','BOOK_choose_cuser')
        ],
        'pluginOptions' => [
            'allowClear' => true
        ],
    ]); ?>


    <?= $form->field($model,'service_id')->dropDownList(\common\models\Services::getServicesMap(),[
        'prompt' => Yii::t('app/book','BOOK_choose_service')
    ])?>

    <?= $form->field($model, 'amount')->textInput(['maxlength' => true]) ?>


    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app/book', 'Create') : Yii::t('app/book', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
