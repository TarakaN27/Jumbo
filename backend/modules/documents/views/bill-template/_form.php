<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\BillTemplate */
/* @var $form yii\widgets\ActiveForm */

$this->registerJs('
function checkUseVatState()
{
    var
        useVat = $("#billtemplate-use_vat"),
        vatRate = $("#billtemplate-vat_rate");

    if(useVat.val() == "'.\common\models\BillTemplate::YES.'")
    {
        vatRate.removeAttr("disabled");
    }else{
        vatRate.val("");
        vatRate.attr("disabled","disabled");
    }
}
',\yii\web\View::POS_END);
$this->registerJs('
    $("#billtemplate-use_vat").on("change",checkUseVatState);
',\yii\web\View::POS_READY);

?>

<div class="bill-template-form">

    <?php $form = ActiveForm::begin([
        'options' => [
            'class' => 'form-horizontal form-label-left'
        ],
        'fieldConfig' => [
            'template' => '<div class="form-group">{label}<div class="col-md-6 col-sm-6 col-xs-12">{input}</div><ul class="parsley-errors-list" >{error}</ul></div>',
            'labelOptions' => ['class' => 'control-label col-md-3 col-sm-3 col-xs-12'],
        ],
    ]); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'l_person_id')->dropDownList(
        \common\models\LegalPerson::getLegalPersonMap(),[
            'prompt' => Yii::t('app/documents','Choose legal person')
        ]
    ) ?>

    <?= $form->field($model, 'service_id')->dropDownList(
        \common\models\Services::getServicesMap(),
        [
            'prompt' => Yii::t('app/documents','Choose service')
        ]
    )?>

    <?= $form->field($model, 'object_text')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>


    <?php
        $arConf =['maxlength' => true];
        if($model->use_vat != \common\models\BillTemplate::YES)
            $arConf['disabled'] = 'disabled';
    ?>

    <?= $form->field($model, 'use_vat')->dropDownList(
        \common\models\BillTemplate::getYesNo()
    ) ?>

    <?= $form->field($model, 'vat_rate')->textInput($arConf) ?>

    <div class="form-group">
        <div class = "col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
            <?= Html::submitButton($model->isNewRecord ? Yii::t('app/documents', 'Create') : Yii::t('app/documents', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
