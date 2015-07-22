<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 13.07.15
 */
use yii\helpers\Html;
use \yii\bootstrap\ActiveForm;
$this->title = Yii::t('app/users', 'Add_invite');
?>
<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2><?php echo \yii\helpers\Html::encode($this->title);?></h2>
                <section class="pull-right">
                    <?= Html::a(Yii::t('app/users', 'To list'), ['index'], ['class' => 'btn btn-warning']) ?>
                </section>
                <div class="clearfix"></div>
             </div>
            <div class = "x_content">
                <br />
                <?php $form = ActiveForm::begin([
                    'options' => [
                        'class' => 'form-horizontal form-label-left'
                        ],
                    'fieldConfig' => [
                        'template' => '<div class="form-group">{label}<div class="col-md-6 col-sm-6 col-xs-12">{input}</div><ul class="parsley-errors-list" >{error}</ul></div>',
                            'labelOptions' => ['class' => 'control-label col-md-3 col-sm-3 col-xs-12'],
                        ],
                    ]); ?>

                <?= $form->field($model, 'email')->textInput(['maxlength' => TRUE]) ?>

                <?= $form->field($model, 'user_type')->dropDownList(\backend\models\BUser::getRoleArrWithRights()) ?>

                <div class = "form-group">
                    <div class = "col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                        <?=Html::submitButton(Yii::t('app/users', 'Send') ,['class' => 'btn btn-success'])?>
                    </div>
                </div>

                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>