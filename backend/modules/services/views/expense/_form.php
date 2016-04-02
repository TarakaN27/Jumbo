<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\ExpenseCategories */
/* @var $form yii\widgets\ActiveForm */
$this->registerJs("
$('#expensecategories-parent_id').on('change',function(){
    var
        check = $('#expensecategories-without_cuser');

    if($(this).val() == undefined || $(this).val() == '')
        {
            check.prop('checked', false );
            check.attr('disabled','disabled');
        }else{
            check.removeAttr('disabled');
        }
});
",\yii\web\View::POS_READY);
?>

<div class="expense-categories-form">

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

    <?= $form->field($model, 'parent_id')->dropDownList(
        \common\models\ExpenseCategories::getParentCat($model->isNewRecord ? NULL : $model->id),[
        'prompt' => Yii::t('app/services','EXPANSE_choose_parent_cat')
    ]) ?>

    <?= $form->field($model, 'status')->dropDownList(
        \common\models\ExpenseCategories::getStatusArr()
    ) ?>
    <div class="form-group">
        <div class = "col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
        <?php
            $options['disabled'] = 'disabled';
            if(!empty($model->parent_id))
                $options = [];

            echo $form->field($model,'without_cuser')->checkbox($options)?>
        </div>
    </div>
    <div class="form-group">
        <div class = "col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
            <?php echo $form->field($model,'ignore_at_report')->checkbox()?>
        </div>
    </div>
    <div class="form-group">
        <div class = "col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
            <?php echo $form->field($model,'private')->checkbox()?>
        </div>
    </div>
    <div class="form-group">
        <div class = "col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app/services', 'Create') : Yii::t('app/services', 'Update btn'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
     </div>
        </div>

    <?php ActiveForm::end(); ?>

</div>
