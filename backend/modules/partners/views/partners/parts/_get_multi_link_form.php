<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 13.5.16
 * Time: 12.02
 */
use yii\helpers\Html;
?>
<?php $form = \yii\bootstrap\ActiveForm::begin([
        'options' => [
                'onsubmit' => 'return false;'
        ]
])?>
<?=$form->field($model,'date')->textInput(['class' => 'datePicker form-control']);?>
<div class="wm-select-2-style wm-select-100percent">
<?=$form->field($model,'cntr')->dropDownList([],[
    'class' => 'wm-select2 form-control'
]); ?>
</div>
<?=$form->field($model,'services')->checkboxList($arServ,[
        'class' => 'servicesGroups'
]);?>
<div class="form-group">
        <?= Html::submitButton(Yii::t('app/users', 'Add'), [
            'id' => 'idAddLinks',
            'class' => 'btn btn-success'
        ]) ?>
</div>
<?php \yii\bootstrap\ActiveForm::end();?>
