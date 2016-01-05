<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\BUserCrmGroup */
/* @var $form yii\widgets\ActiveForm */
$this->registerCss('
#busercrmgroup-log_work_type{
    padding-top:8px;
}
');
?>

<div class="buser-crm-group-form">

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

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'role_id')->dropDownList(\common\models\BUserCrmRoles::getRoleMap(),[
        'prompt' => Yii::t('app/crm','Choose role')
    ]) ?>


    <?=$form->field($model,'log_work_type')->radioList(\common\models\BUserCrmGroup::getLogWorkTypeArr())?>

    <div class="form-group">
        <div class = "col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app/config', 'Create') : Yii::t('app/config', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
