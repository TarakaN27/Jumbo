<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$arServices = \common\models\Services::getAllServices();
$arLP = \common\models\LegalPerson::getLegalPersonMap();

/* @var $this yii\web\View */
/* @var $model common\models\BonusScheme */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="bonus-scheme-form">

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

    <?= $form->field($model, 'type')->dropDownList(
        $model::getTypeMap(),[
        'prompt' => Yii::t('app/bonus','Choose type')
    ]) ?>

    <?= $form->field($model, 'num_month')->textInput() ?>

    <?= $form->field($model, 'inactivity')->textInput() ?>

    <?= $form->field($model, 'grouping_type')->dropDownList($model::getGroupByMap(),[
        'prompt' => Yii::t('app/bonus','Choose grouping type')
    ]) ?>
    <?php foreach($arServices as $serv):?>
        <div class="col-md-4 col-sm-4 col-xs-12">
            <?=Html::tag('h4',$serv->name);?>
            <div class="row">
                <div class="col-md-6 col-sm-6 col-xs-12">
                <?php foreach($arLP as $lp):?>
                    

                <?php endforeach;?>
                </div>
                <div class="col-md-6 col-sm-6 col-xs-12">

                </div>
            </div>
        </div>
    <?php endforeach;?>
    <div class="form-group">
        <div class = "col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app/bonus', 'Create') : Yii::t('app/bonus', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
