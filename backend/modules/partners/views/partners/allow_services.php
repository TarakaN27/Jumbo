<?php
/**
 *
 * @var PsiWhiteSpace $obPartner
 * @var \backend\modules\partners\models\PartnerAllowForm $model
 */
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
?>
<div class = "row">
    <div class = "col-md-12 col-sm-12 col-xs-12">
        <div class = "x_panel">
            <div class = "x_title">
                <h2><?php echo Html::encode($this->title)?></h2>
                <section class="pull-right">
                    <?= Html::a(Yii::t('app/services', 'To list'), ['index'], ['class' => 'btn btn-warning']) ?>
                </section>
                <div class = "clearfix"></div>
            </div>
            <div class = "x_content">
                <?php $form = ActiveForm::begin();?>
                <?=$form->field($model,'services')->widget(\common\components\multiSelect\MultiSelectWidget::className(),[
                    'data' => \common\models\Services::getServicesMap(),
                    'clientOptions' => [
                        //'selectableHeader' => Yii::t('app/reports','Services'),
                        //'selectionHeader' => Yii::t('app/reports','Selected services')
                    ]
                ])?>
                <div class="form-group">
                    <div class = "col-md-6 col-sm-6 col-xs-12">
                        <?= Html::submitButton(Yii::t('app/users', 'Save'), ['class' => 'btn btn-success']) ?>
                    </div>
                </div>
                <?php ActiveForm::end();?>
            </div>
        </div>
    </div>
</div>
