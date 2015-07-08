<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\jui\DatePicker;
/* @var $this yii\web\View */
/* @var $model common\models\Payments */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="payments-form">

    <?php $form = ActiveForm::begin([
        'options' => [
            'class' => 'form-horizontal form-label-left'
        ],
        'fieldConfig' => [
            'template' => '<div class="form-group">{label}<div class="col-md-6 col-sm-6 col-xs-12">{input}</div><ul class="parsley-errors-list" >{error}</ul></div>',
            'labelOptions' => ['class' => 'control-label col-md-3 col-sm-3 col-xs-12'],
        ],
    ]); ?>

    <?= $form->field($model, 'cuser_id')->dropDownList(\common\models\CUser::getContractorMap(),['prompt' => Yii::t('app/book','BOOK_choose_cuser')]) ?>

    <?= $form->field($model, 'pay_date')->widget(DatePicker::className(),[
        'dateFormat' => 'dd-MM-yyyy',
        'clientOptions' => [
            'defaultDate' => date('d-m-Y',time())
        ],
        'options' => [
            'class' => 'form-control'
        ]
    ]) ?>

    <?= $form->field($model, 'pay_summ')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'currency_id')->dropDownList(\common\models\ExchangeRates::getRatesCodes(),['prompt' => Yii::t('app/book','BOOK_choose_currency')]) ?>

    <?= $form->field($model, 'service_id')->dropDownList(\common\models\Services::getServicesMap(),['prompt' => Yii::t('app/book','BOOK_choose_service')]) ?>

    <?= $form->field($model, 'legal_id')->dropDownList(\common\models\LegalPerson::getLegalPersonMap(),['prompt' => Yii::t('app/book','BOOK_choose_legal_person')]) ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

    <div class="form-group">
        <div class = "col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app/book', 'Create') : Yii::t('app/book', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div></div>

    <?php ActiveForm::end(); ?>

</div>
