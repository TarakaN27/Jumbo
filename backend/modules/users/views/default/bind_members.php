<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 29.07.15
 */
use yii\helpers\Html;
?>
    <div class="row">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Закрепление сотрудников</h2>
                    <section class="pull-right">
                        <?= Html::a(Yii::t('app/users', 'To list'), ['index'], ['class' => 'btn btn-warning']) ?>
                    </section>
                    <div class="clearfix"></div>
                    <span class="label label-info" style="color: #FFFFFF;">Info</span>
                            <p><?=Yii::t('app/users','Bind_BUser_Instruction')?></p>
                    <div class="clearfix"></div>
                </div>

                <?php $form = \yii\bootstrap\ActiveForm::begin([
                    'options' => [
                        'class' => 'form-horizontal form-label-left'
                    ],
                    'fieldConfig' => [
                        'template' => '<div class="form-group">{label}<div class="col-md-6 col-sm-6 col-xs-12">{input}</div><ul class="parsley-errors-list" >{error}</ul></div>',
                        'labelOptions' => ['class' => 'control-label col-md-3 col-sm-3 col-xs-12'],
                    ],
                ]);?>
                <?=$form->field($model,'members')->widget(\common\components\multiSelect\MultiSelectWidget::className(),[
                    'data' => \backend\models\BUser::getAllMembersMap(Yii::$app->user->id),
                    'clientOptions' => [
                        'selectableHeader' => Yii::t('app/users','Busers'),
                        'selectionHeader' => Yii::t('app/users','Binded')
                    ]
                ])?>
                    <div class = "form-group">
                        <div class = "col-md-6 col-sm-6 col-xs-12 col-md-offset-3">

                            <?=Html::submitButton(Yii::t('app/users', 'Save') ,
                                ['class' => 'btn btn-success'])?>
                        </div>
                    </div>
                <?php \yii\bootstrap\ActiveForm::end();?>
            </div>
        </div>
    </div>