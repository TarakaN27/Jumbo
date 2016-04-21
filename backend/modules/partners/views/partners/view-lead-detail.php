<?php
/**
 *
 */
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
?>
<div class = "row">
    <div class = "col-md-12 col-sm-12 col-xs-12">
        <div class = "x_panel">
            <div class = "x_title">
                <h2><?php echo Html::encode($this->title)?></h2>
                <section class="pull-right">
                    <?= Html::a(Yii::t('app/users', 'To list'), ['index'], ['class' => 'btn btn-warning']) ?>
                </section>
                <div class = "clearfix"></div>
            </div>
            <div class = "x_content">
                <div class="row">
                    <div class = "col-md-6 col-sm-6 col-xs-12 ">
                        <div class = "col-md-4 col-sm-4 col-xs-12">
                            <?php $form = ActiveForm::begin([]);?>
                            <?=$form->field($model,'beginDate')->widget(\kartik\date\DatePicker::className(),[
                                'options' => [
                                    'class' => 'form-control'
                                ],
                                'pluginOptions' => [
                                    'autoclose' => TRUE,
                                    'format' => 'dd.mm.yyyy',
                                    'defaultDate' => date('d.m.Y', time())
                                ]
                            ])?>
                        </div>
                        <div class = "col-md-4 col-sm-4 col-xs-12">
                            <?=$form->field($model,'endDate')->widget(\kartik\date\DatePicker::className(),[
                                'options' => [
                                    'class' => 'form-control'
                                ],
                                'pluginOptions' => [
                                    'autoclose' => TRUE,
                                    'format' => 'dd.mm.yyyy',
                                    'defaultDate' => date('d.m.Y', time())
                                ]
                            ])?>
                        </div>
                        <div class="col-md-4 col-sm-4 col-xs-12 ppd-top-23">
                            <div class="form-group text-center">
                                <?= Html::submitButton(Yii::t('app/reports', 'Get report'), ['class' => 'btn btn-success']) ?>
                            </div>
                        </div>
                        <?php ActiveForm::end();?>
                    </div>


                </div>
                <div class="row">
                    <?=$this->render('./parts/lead_detail',['data'=>$data,'model' => $model]);?>
                </div>
            </div>
        </div>
    </div>
</div>
