<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 03.08.15
 */
use yii\helpers\Html;
use backend\modules\reports\forms\ExpenseReportForm;
$this->title = Yii::t('app/reports','Expense reports');
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
                    <p><span class="label label-info">Info</span> <?=Yii::t('app/reports','Expense reports help info')?></p>
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
                            <?=$form->field($model,'paymentCategory')->widget(\common\components\multiSelect\MultiSelectWidget::className(),[
                                'data' => \common\models\ExpenseCategories::getExpenseCatTreeGroupSelectable(),
                                'clientOptions' => [
                                    //'selectableHeader' => Yii::t('app/reports','Services'),
                                    //'selectionHeader' => Yii::t('app/reports','Selected services')
                                ]
                            ])?>
                        </div>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                             <?=$form->field($model,'contractor')->widget(\common\components\multiSelect\MultiSelectWidget::className(),[
                                 'data' => $arContractorMap,
                                 'clientOptions' => [
                                     //'selectableHeader' => Yii::t('app/reports','Contractors'),
                                     //'selectionHeader' => Yii::t('app/reports','Selected Contractors')
                                 ]
                             ])?>
                        </div>
                        <?php if(Yii::$app->user->can('adminRights')):?>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <?=$form->field($model,'legalPerson')->widget(\common\components\multiSelect\MultiSelectWidget::className(),[
                                    'data' => \common\models\LegalPerson::getLegalPersonMap(),
                                    'clientOptions' => [
                                        //'selectableHeader' => Yii::t('app/reports','Contractors'),
                                        //'selectionHeader' => Yii::t('app/reports','Selected Contractors')
                                    ]
                                ])?>
                            </div>
                        <?php endif;?>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <div class="row">
                                <div class="col-md-6 col-sm-6 col-xs-12 lineAfter">
                                    <?=$form->field($model,'dateFrom')->widget(\kartik\date\DatePicker::className(),[
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
                                <div class="col-md-6 col-sm-6 col-xs-12 lineAfter">
                                    <?=$form->field($model,'dateTo')->widget(\kartik\date\DatePicker::className(),[
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
                            </div>
                            <div class="row">
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <?=$form->field($model,'groupType')->radioList(ExpenseReportForm::getGroupByMap())?>
                                </div>

                                <div class="col-md-6 col-sm-6 col-xs-12 ">
                                    <?php if(Yii::$app->user->can('adminRights')):?>
                                        <?=Html::label(Yii::t('app/reports','Documents'))?>
                                        <?=$form->field($model,'generateExcel')->checkbox();?>
                                        <?php //echo $form->field($model,'generateExtendExcel')->checkbox();?>
                                        <hr/>
                                    <?php endif;?>
                                </div>

                            </div>
                        </div>
                    </div>


                    <div class="form-group text-center">
                            <?= Html::submitButton(Yii::t('app/reports', 'Get report'), ['class' => 'btn btn-success']) ?>
                    </div>
                <?php \yii\bootstrap\ActiveForm::end();?>
                    <hr/>
                    <?php if(!empty($arData)):?>
                    <div class = "row no-print">
                        <div class = "col-xs-12 text-center">
                            <button class = "btn btn-default" onclick = "window.print();">
                                <i class = "fa fa-print"></i> <?=Yii::t('app/reports','Print')?>
                            </button>
                            <?php if(!empty($arData['excelLink'])):?>
                                <?=Html::a('<i class="fa fa-download"></i> '.Yii::t('app/reports','Get excel report'),[
                                    '/site/get-document','name' => $arData['excelLink'],'hidfold' => 'reports'],
                                    [
                                        'target' => '_blank',
                                        'class' => "btn btn-default"
                                    ]
                                )?>
                            <?php endif;?>
                            <?php if(!empty($arData['excelExtendLink'])):?>
                                <?=Html::a('<i class="fa fa-download"></i> '.Yii::t('app/reports','Get extended excel report'),[
                                    '/site/get-document','name' => $arData['excelExtendLink'],'hidfold' => 'reports'],
                                    [
                                        'target' => '_blank',
                                        'class' => "btn btn-default"
                                    ]
                                )?>
                            <?php endif;?>
                        </div>
                    </div>
                    <hr/>
                    <?php endif;?>
                </div>
                <div class="x_content">
                    <?php if(!empty($arData)):?>
                        <?= $this->render('_part_table_view', [
                            'model' => $arData,
                            'modelForm' => $model
                        ]) ?>
                    <?php else:?>
                        <p><?=Yii::t('app/reports','No data');?></p>
                    <?php endif;?>
                </div>
            </div>
        </div>
    </div>

