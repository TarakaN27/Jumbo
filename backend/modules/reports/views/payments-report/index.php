<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 03.08.15
 */
use yii\helpers\Html;
$this->title = Yii::t('app/reports','Payments reports');
?>
    <div class="row">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2><?= Html::encode($this->title) ?></h2>
                    <section class="pull-right">
                    </section>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                <?php $form = \yii\bootstrap\ActiveForm::begin([
                    'options' => [
                       // 'class' => 'form-inline'
                    ],
                   // 'fieldConfig' => [
                   //     'template' => '<div class="form-group">{label}<div class="col-md-6 col-sm-6 col-xs-12">{input}</div><ul class="parsley-errors-list" >{error}</ul></div>',
                   //     'labelOptions' => ['class' => 'control-label col-md-3 col-sm-3 col-xs-12'],
                   // ],
                ]);?>
                    <div class="row">
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <?=$form->field($model,'services')->widget(\common\components\multiSelect\MultiSelectWidget::className(),[
                                'data' => \common\models\Services::getServicesMap(),
                                'clientOptions' => [
                                    'selectableHeader' => Yii::t('app/reports','Services'),
                                    'selectionHeader' => Yii::t('app/reports','Selected services')
                                ]
                            ])?>
                        </div>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                             <?=$form->field($model,'contractor')->widget(\common\components\multiSelect\MultiSelectWidget::className(),[
                                 'data' => \common\models\CUser::getContractorMap(),
                                 'clientOptions' => [
                                     'selectableHeader' => Yii::t('app/reports','Contractors'),
                                     'selectionHeader' => Yii::t('app/reports','Selected Contractors')
                                 ]
                             ])?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 col-sm-6 col-xs-12">
                                <?=$form->field($model,'dateFrom')->widget(\kartik\date\DatePicker::className(),[
                                    'options' => [
                                        'class' => 'form-control'
                                    ],
                                    'pluginOptions' => [
                                        'autoclose' => TRUE,
                                        'format' => 'yyyy-mm-dd',
                                        'defaultDate' => date('Y-m-d', time())
                                    ]
                                ])?>
                        </div>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <?=$form->field($model,'dateTo')->widget(\kartik\date\DatePicker::className(),[
                                'options' => [
                                    'class' => 'form-control'
                                ],
                                'pluginOptions' => [
                                    'autoclose' => TRUE,
                                    'format' => 'yyyy-mm-dd',
                                    'defaultDate' => date('Y-m-d', time())
                                ]
                            ])?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 col-sm-6 col-xs-12">
                               <?=$form->field($model,'generateExcel')->checkbox();?>
                               <?=$form->field($model,'generateDocx')->checkbox();?>
                            </div>
                            <div class="col-md-6 col-sm-6 col-xs-12">

                            </div>
                    </div>
                    <div class="form-group">
                            <?= Html::submitButton(Yii::t('app/services', 'Get report'), ['class' => 'btn btn-success']) ?>
                    </div>
                <?php \yii\bootstrap\ActiveForm::end();?>
                    <hr/>
                </div>
                <div class="x_content">
                    <?php if(!empty($arData)):?>
                        <?= $this->render('_part_table_view', [
                            'model' => $arData,
                        ]) ?>
                    <?php else:?>
                        <p><?=Yii::t('app/reports','No data');?></p>
                    <?php endif;?>
                    <hr/>
                    <div class = "row no-print">
                        <div class = "col-xs-12">
                            <button class = "btn btn-default" onclick = "window.print();">
                                <i class = "fa fa-print"></i> Print
                            </button>
                            <?php if(!empty($arData['excelLink'])):?>
                                <?=Html::a(Yii::t('app/reports','Get excel report'),[
                                    '/site/get-document','name' => $arData['excelLink'],'hidfold' => 'reports'],
                                    [
                                        'target' => '_blank',
                                        'class' => "btn btn-default"
                                    ]
                                )?>
                            <?php endif;?>
                            <?php if(!empty($arData['docxLink'])):?>
                                <?=Html::a(Yii::t('app/reports','Get docx report'),[
                                        '/site/get-document','name' => $arData['docxLink'],'hidfold' => 'reports'],
                                    [
                                        'target' => '_blank',
                                        'class' => "btn btn-default"
                                    ]
                                )?>
                            <?php endif;?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

