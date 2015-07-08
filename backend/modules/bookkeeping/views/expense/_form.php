<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\jui\DatePicker;

/* @var $this yii\web\View */
/* @var $model common\models\Expense */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="expense-form">

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

    <?= $form->field($model, 'cat_id')->dropDownList(\common\models\ExpenseCategories::getExpenseCatMap(),['prompt' => Yii::t('app/book','BOOK_choose_expense_category')]) ?>

    <?= $form->field($model, 'cuser_id')->dropDownList(\common\models\CUser::getContractorMap(),['prompt' => Yii::t('app/book','BOOK_choose_cuser')])  ?>

    <?= $form->field($model, 'pay_summ')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'currency_id')->dropDownList(\common\models\ExchangeRates::getRatesCodes(),['prompt' => Yii::t('app/book','BOOK_choose_currency')]) ?>

    <?= $form->field($model, 'legal_id')->dropDownList(\common\models\LegalPerson::getLegalPersonMap(),['prompt' => Yii::t('app/book','BOOK_choose_legal_person')]) ?>

    <?= $form->field($model, 'pay_date')->widget(DatePicker::className(),[
        'dateFormat' => 'dd-MM-yyyy',
        'clientOptions' => [
            'defaultDate' => date('d-m-Y',time())
        ],
        'options' => [
            'class' => 'form-control'
        ]
    ]) ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>


    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app/book', 'Create') : Yii::t('app/book', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
