<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\components\helpers\CustomViewHelper;
/* @var $this yii\web\View */
/* @var $model common\models\Enrolls */
/* @var $form yii\widgets\ActiveForm */

CustomViewHelper::registerJsFileWithDependency('@web/js/accounting/accounting.min.js',$this,[],'accounting');
$this->registerJs('
    $("#enrolls-amount,#enrolls-repay,#enrolls-enroll").on("change",function(){
        amountFormatter(this);
    });
    amountFormatter("#enrolls-amount");
    amountFormatter("#enrolls-repay");
    amountFormatter("#enrolls-enroll");
',\yii\web\View::POS_READY);
?>

<div class="enrolls-form">

    <?php $form = ActiveForm::begin([
        'options' => [
            'class' => 'form-horizontal form-label-left',
            //'enctype' => 'multipart/form-data'
        ],
        'fieldConfig' => [
            'template' => '<div class="form-group">{label}<div class="col-md-6 col-sm-6 col-xs-12">{input}</div><ul class="parsley-errors-list" >{error}</ul></div>',
            'labelOptions' => ['class' => 'control-label col-md-3 col-sm-3 col-xs-12'],
        ],
    ]); ?>

    <?= $form->field($model, 'amount')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'repay')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'enroll')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'description')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <div class = "col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app/book', 'Create') : Yii::t('app/book', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
