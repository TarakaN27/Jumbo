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
                    'fieldConfig' => [
                        'template' => '<div class="form-group">{label}<div class="col-md-6 col-sm-6 col-xs-12">{input}</div><ul class="parsley-errors-list" >{error}</ul></div>',
                        'labelOptions' => ['class' => 'control-label col-md-4 col-sm-4 col-xs-12'],
                    ],
                ]); ?>

                <?= $form->field($model, 'src')->fileInput(); ?>
                <div class="form-group">
                    <?= Html::submitButton(Yii::t('app/book', 'Upload'),
                        ['class' => 'btn btn-success']
                    ) ?>
                </div>

                <?php ActiveForm::end(); ?>

            </div>
        </div>
    </div>
</div>





