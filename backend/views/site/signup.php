<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 13.07.15
 */
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
$this->title = Yii::t('app/users','Signup');
?>
<?php $form = ActiveForm::begin(['id' => 'login-form']); ?>
    <h1><?php echo Html::encode($this->title);?></h1>
    <div>
        <?= $form->field($model, 'username') ?>
    </div>
    <div>
        <?= $form->field($model, 'password')->passwordInput() ?>
    </div>
    <div>
        <?= $form->field($model, 'password_repeat')->passwordInput() ?>
    </div>
    <div>
        <?= Html::submitButton('Signup', ['class' => 'btn btn-default submit', 'name' => 'signup-button']) ?>
    </div>
    <div class="clearfix"></div>
    <div class="separator">
        <div>
            <h1><?php echo Html::img('@web/images/logo.png',['alt'=>'Webmart Logo']);?> Webmart Group.</h1>
            <p>©<?php echo date('Y',time())?> All Rights Reserved. Webmart Group. Privacy and Terms</p>
        </div>
    </div>
<?php ActiveForm::end(); ?>