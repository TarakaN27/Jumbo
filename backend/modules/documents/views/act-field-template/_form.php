<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\Services;
use common\models\LegalPerson;
/* @var $this yii\web\View */
/* @var $model common\models\ActFieldTemplate */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="act-field-template-form">

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

    <?= $form->field($model, 'service_id')->dropDownList(Services::getServicesMap(),[
        'prompt' => Yii::t('app/book','Choose service')
    ]) ?>

    <?= $form->field($model, 'legal_id')->dropDownList(LegalPerson::getLegalPersonMap(),[
        'prompt' => Yii::t('app/book','Choose legal person')
    ]) ?>

    <?= $form->field($model, 'job_name')->textarea(['rows' => 6]) ?>

    <div class="form-group">
        <div class = "col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app/book', 'Create') : Yii::t('app/book', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
