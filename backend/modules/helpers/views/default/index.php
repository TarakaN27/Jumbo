<?php

use yii\helpers\Html;
?>


<?php $form = \yii\bootstrap\ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]);?>

    <?=$form->field($model,'file')->fileInput()?>
    <?=$form->field($model,'manager')->dropDownList(\backend\models\BUser::getListManagers())?>
    <?=$form->field($model,'type')->dropDownList(\common\models\CUserTypes::getUserTypesMap())?>

    <div class="form-group">
        <div class = "col-md-offset-8 pull-right">
        <?= Html::submitButton(Yii::t('app/users', 'Create') , ['class' => 'btn btn-primary']) ?>
        </div>
        </div>
<?php \yii\bootstrap\ActiveForm::end();?>