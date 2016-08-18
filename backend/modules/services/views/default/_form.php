<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\web\JsExpression;
/* @var $this yii\web\View */
/* @var $model common\models\Services */
/* @var $form yii\widgets\ActiveForm */

$this->registerJs("
function enrollmentAction(clean)
{
    var
        check = $('#services-allow_enrollment'),
        sel = $('#services-b_user_enroll');        

    if(check.is(':checked'))
    {
        sel.removeAttr('disabled');        
    }else{
        if(clean)
        {
            sel.val('');            
        }
        sel.attr('disabled','disabled');        
    }
}
",\yii\web\View::POS_END);

$this->registerJs("
enrollmentAction(false);
$('#services-allow_enrollment').on('change',function(){
    enrollmentAction(true);
});
",\yii\web\View::POS_READY);
?>
<div class="services-form">
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

    <?= $form->field($model, 'description')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'rate')->textInput();?>

    <?= $form->field($model, 'status')->dropDownList(\common\models\Services::getStatusArr()) ?>

    <div class="form-group">
        <div class = "col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
            <?= $form->field($model,'allow_enrollment')->checkbox()?>
        </div>
    </div>
    <?= $form->field($model,'b_user_enroll')->widget(\kartik\select2\Select2::className(),[
        'initValueText' => $sAssName, // set the initial display text
        'options' => [
            'placeholder' => Yii::t('app/crm','Search for a users ...'),
            'disabled' => 'disabled'
        ],
        'pluginOptions' => [
            'allowClear' => true,
            'minimumInputLength' => 2,
            'ajax' => [
                'url' => \yii\helpers\Url::to(['/ajax-select/get-b-user']),
                'dataType' => 'json',
                'data' => new JsExpression('function(params) { return {q:params.term}; }')
            ],
            'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
            'templateResult' => new JsExpression('function(cmp_id) { return cmp_id.text; }'),
            'templateSelection' => new JsExpression('function (cmp_id) { return cmp_id.text; }'),
        ],
    ])?>


    <div class="form-group">
         <div class = "col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
            <?= Html::submitButton(
                $model->isNewRecord ? Yii::t('app/services', 'Create') : Yii::t('app/services', 'Update btn'),
                ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']
            ) ?>
         </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>
