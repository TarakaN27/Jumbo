<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\jui\DatePicker;
use kartik\select2\Select2;
use yii\web\JsExpression;
use common\components\helpers\CustomViewHelper;

/* @var $this yii\web\View */
/* @var $model common\models\Expense */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2><?= Html::encode($this->title) ?></h2>
                <section class="pull-right">
                    <?= Html::a(Yii::t('app/book', 'To list'), ['index'], ['class' => 'btn btn-warning']) ?>
                </section>
                <div class="clearfix"></div>
            </div>
            <div class="expense-form">
                <?php $form = ActiveForm::begin([
                    'options' => [
                        'class' => 'form-horizontal form-label-left'
                    ],
                    'enableClientValidation' => false,
                    'fieldConfig' => [
                        'template' => '<div class="form-group">{label}<div class="col-md-12 col-sm-12 col-xs-12">{input}</div><ul class="parsley-errors-list" >{error}</ul></div>',
                        'labelOptions' => ['class' => 'control-label col-md-4 col-sm-4 col-xs-12'],
                    ],
                ]); ?>
                <table class="table col-md-12">
                    <thead>
                    <th>

                    </th>
                    <th>
                        <?= Yii::t('app/book', 'Cuser ID'); ?>
                    </th>
                    <th>
                        <?= Yii::t('app/book', 'Is Unknown'); ?>
                    </th>
                    <th>
                        <?= Yii::t('app/book', 'Manager ID'); ?>
                    </th>
                    <th>
                        <?= Yii::t('app/book', 'Legal ID'); ?>
                    </th>
                    <th>
                        <?= Yii::t('app/book', 'Pay Date'); ?>
                    </th>
                    <th>
                        <?= Yii::t('app/book', 'Pay Summ'); ?>
                    </th>
                    <th>
                        <?= Yii::t('app/book', 'Currency ID'); ?>
                    </th>
                    <th>
                        <?= Yii::t('app/book', 'Payment order'); ?>
                    </th>
                    <th>
                        <?= Yii::t('app/book', 'Description'); ?>
                    </th>

                    </thead>
                    <tbody>
                    <? foreach ($models as $key=>$model) { ?>
                        <tr>
                            <td><?= $form->field($model, "[{$key}]active")->checkbox(['label' =>""]);?>
                            </td>
                            <td style="max-width: 200px;" width ="15%">
                                <?if(!$model->is_unknown){?>
                                <?php  echo $form->field($model, "[{$key}]cntr_id")->widget(Select2::classname(), [
                                    'data' => \common\models\CUser::getContractorMap(),
                                    'options' => ['placeholder' => Yii::t('app/book','BOOK_choose_cuser')],
                                    'pluginOptions' => [
                                        'allowClear' => true
                                    ],
                                ])->label(false); ?>
                                <?}else{?>
                                    <?php echo $form->field($model,"[{$key}]user_name",['options' => ['class' => 'form-group']])->textInput()->label(false);?>
                                <?}?>
                            </td>
                            <td>
                                <?php echo $form->field($model,"[{$key}]is_unknown")->dropDownList(\common\models\PaymentRequest::getYesNo())->label(false);?>
                            </td>
                            <td>
                                <?php  echo $form->field($model, "[{$key}]manager_id")->widget(Select2::classname(), [
                                    'data' => \backend\models\BUser::getAllMembersMap(),
                                    'options' => ['placeholder' => Yii::t('app/book','BOOK_choose_managers')],
                                    'pluginOptions' => [
                                        'allowClear' => true
                                    ],
                                ])->label(false); ?>
                            </td>
                            <td>
                                <?= $form->field($model, "[{$key}]legal_id")->dropDownList(\common\models\LegalPerson::getLegalPersonMap())->label(false);?>
                            </td>

                            <td width ="10%">
                                <?= $form->field($model, "[{$key}]pay_date")->widget(DatePicker::className(), [
                                    'dateFormat' => 'dd.MM.yyyy',
                                    'clientOptions' => [
                                        'defaultDate' => date('d.m.Y', time())
                                    ],
                                    'options' => [
                                        'class' => 'form-control'
                                    ]
                                ])->label(false); ?>
                            </td>
                            <td width ="10%">
                                <?= $form->field($model, "[{$key}]pay_summ", ['options' => [
                                ]])->textInput(['maxlength' => true])->label(false) ?>
                            </td>
                            <td width ="7%">
                                <?= $form->field($model, "[{$key}]currency_id", ['options' => [
                                ]])
                                    ->dropDownList(\common\models\ExchangeRates::getRatesCodes())->label(false) ?>
                            </td>

                            <td>
                                <?= $form->field($model,"[{$key}]payment_order")->textInput()->label(false);?>
                            </td>
                            <td>
                            <?= $form->field($model, "[{$key}]service_id")->dropDownList(\common\models\Services::getServicesMap(),[
                                'prompt' => Yii::t('app/book','Choose service')
                            ])->label(false)?>
                            </td>
                            <td >
                                <?= $form->field($model, "[{$key}]description")->textarea(['rows' => 2])->label(false); ?>
                            </td>
                        </tr>
                    <? } ?>
                </table>
                <div class="form-group">
                    <?= Html::submitButton(
                        $model->isNewRecord ? Yii::t('app/book', 'Create') : Yii::t('app/book', 'Update btn'),
                        ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']
                    ) ?>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>





