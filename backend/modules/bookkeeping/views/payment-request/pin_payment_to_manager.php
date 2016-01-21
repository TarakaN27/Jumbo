<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 24.07.15
 */
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
?>
<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2><?= Html::encode($this->title) ?><small>Назначение контрагента для запроса</small></h2>
                <section class="pull-right">
                    <?= Html::a(Yii::t('app/book', 'To list'), ['index'], ['class' => 'btn btn-warning']) ?>
                </section>
                <div class="clearfix"></div>
            </div>
            <?php $form = ActiveForm::begin([
                'options' => [
                    'class' => 'form-horizontal form-label-left'
                ],
                'fieldConfig' => [
                    'template' => '{label}<div class="col-md-6 col-sm-6 col-xs-12">{input}</div><ul class="parsley-errors-list" >{error}</ul>',
                    'labelOptions' => ['class' => 'control-label col-md-3 col-sm-3 col-xs-12'],
                ],
            ]); ?>
            <?=$form->field($model,'contractor')->dropDownList($arContrMap,['prompt' => Yii::t('app/book','Choose contractor')])?>
            <div class="form-group">
                <div class = "col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                <?= Html::submitButton(Yii::t('app/book', 'Save'), ['class' => 'btn btn-success']) ?>
                </div>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>

