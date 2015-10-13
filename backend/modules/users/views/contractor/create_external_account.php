<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 1.10.15
 * Time: 12.03
 */
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
$this->title = Yii::t('app/users', 'Create external account');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/users', 'Cusers'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2><?php echo Html::encode($this->title);?></h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <?php $form = ActiveForm::begin([
                    'options' => [
                        'class' => 'form-horizontal form-label-left'
                    ],
                    'fieldConfig' => [
                        'template' => '<div class="form-group">{label}<div class="col-md-6 col-sm-6 col-xs-12">{input}</div><ul class="parsley-errors-list" >{error}</ul></div>',
                        'labelOptions' => ['class' => 'control-label col-md-3 col-sm-3 col-xs-12'],
                    ],
                ]); ?>

                <?php echo $form->field($obModel,'login')->textInput()?>
                <?php echo $form->field($obModel,'email')->textInput()?>
                <?php echo $form->field($obModel,'password')->passwordInput()?>
                <?php echo $form->field($obModel,'passwordConfirm')->passwordInput()?>

                <div class="form-group">
                    <div class = "col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                        <?= Html::submitButton(Yii::t('app/services', 'Create'),['class' => 'btn btn-success']) ?>
                    </div>
                </div>

                <?php ActiveForm::end(); ?>
            </div>

        </div>
    </div>
</div>
