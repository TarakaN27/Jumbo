<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\jui\DatePicker;
/* @var $this yii\web\View */
/* @var $model common\models\Payments */
/* @var $form yii\widgets\ActiveForm */
$fieldTpl = '<div>{input}</div><ul class="parsley-errors-list" >{error}</ul>';
//$labelOptions = ['class' => 'control-label'];
?>

<div class="payments-form">

    <?php $form = ActiveForm::begin([
        'options' => [
            'class' => 'form-horizontal form-label-left'
        ],
        'fieldConfig' => [
            'template' => '{label}<div class="col-md-6 col-sm-6 col-xs-12">{input}</div><ul class="parsley-errors-list" >{error}</ul>',
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

    <div class="form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="payments-service_id"><?php echo Html::activeLabel($model,'pay_summ');?></label>
        <div class='col-md-6 col-sm-6 col-xs-12'>
            <?= $form->field($model, 'pay_summ',['template' => $fieldTpl,'options' => [
                'class' => 'col-md-8 col-sm-8 col-xs-12',
                'style' => 'padding-left:0px;'
            ]])
                ->textInput(['maxlength' => true])->label(false) ?>
            <?= $form->field($model, 'currency_id',['template' => $fieldTpl,'options' => [
                'class' => 'col-md-4 col-sm-4 col-xs-12',
                'style' => 'padding-right:0px;'
            ]])
                ->dropDownList(\common\models\ExchangeRates::getRatesCodes())->label(false) ?>
        </div>
    </div>
    <?= $form->field($model, 'service_id')->dropDownList(\common\models\Services::getServicesMap(),['prompt' => Yii::t('app/book','BOOK_choose_service')]) ?>

    <?= $form->field($model, 'legal_id')->dropDownList(\common\models\LegalPerson::getLegalPersonMap()) ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

    <div class="form-group">
        <div class = "col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app/book', 'Create') : Yii::t('app/book', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div></div>

    <?php ActiveForm::end(); ?>

</div>
