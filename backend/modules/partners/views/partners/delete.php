<?php
/**
 *
 */
?>
<?php $form = new \yii\bootstrap\ActiveForm();?>
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
<?php \yii\bootstrap\ActiveForm::end();?>



