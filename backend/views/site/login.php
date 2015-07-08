<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \common\models\LoginForm */

$this->title = 'Login';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $form = ActiveForm::begin(['id' => 'login-form']); ?>
                        <h1>Login Form</h1>
                        <div>
                            <?= $form->field($model, 'username') ?>
                        </div>
                        <div>
                            <?= $form->field($model, 'password')->passwordInput() ?>
                        </div>
                        <div>
                            <?= $form->field($model, 'rememberMe')->checkbox() ?>
                            <?= Html::submitButton('Login', ['class' => 'btn btn-default submit', 'name' => 'login-button']) ?>
                        </div>
                        <div class="clearfix"></div>
                        <div class="separator">
                            <div>
                                <h1><?php echo Html::img('@web/images/logo.png',['alt'=>'Webmart Logo']);?> Webmart Group.</h1>
                                <p>©<?php echo date('Y',time())?> All Rights Reserved. Webmart Group. Privacy and Terms</p>
                            </div>
                        </div>
<?php ActiveForm::end(); ?>
