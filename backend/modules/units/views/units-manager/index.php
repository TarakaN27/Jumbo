<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 18.08.15
 */
use yii\helpers\Html;
use yii\data\ArrayDataProvider;
$this->title = Yii::t('app/reports','Detail units table');
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
                    <div class="row">
                        <div class="col-md-3 col-sm-3 col-xs-12">
                            <table class="table table-bordered responsive-utilities jambo_table">
                                <thead>
                                    <tr class="headings">
                                        <th>
                                            <?=Yii::t('app/reports','Total units')?>
                                        </th>
                                        <th>
                                            <?=Yii::t('app/reports','Total cost')?>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <?=!empty($arData) ? $arData['iTotalUnits'] : 'N/A'?>
                                        </td>
                                        <td>
                                            <?=!empty($arData) ? $arData['iTotalCost'] : 'N/A'?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="col-md-9 col-sm-9 col-xs-12">
                            <?$form = \yii\bootstrap\ActiveForm::begin([
                                'options' => [
                                    'class' => 'form-horizontal form-label-left'
                                ]
                            ]);?>
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
                                <div class="col-md-6 col-sm-6 col-xs-12" >
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
                            <div class="form-group pull-right">
                                            <?= Html::submitButton(Yii::t('app/reports', 'Get report'), ['class' => 'btn btn-success']) ?>
                                    </div>
                            <?\yii\bootstrap\ActiveForm::end();?>
                        </div>
                    </div>
            </div>

            <div class="x_content">
                <?=\yii\grid\GridView::widget([
                    'dataProvider' => new ArrayDataProvider([
                            'allModels' => isset($arData['arUnits']) ? $arData['arUnits'] : [],
                        ]),
                    'columns' => [
                        [
                            'attribute' => 'unit_id',
                            'value' => function($model){
                                    return is_object($obUnit = $model->unit) ? $obUnit->name : 'N/A';
                                }
                        ],
                        'cost',
                        [
                            'attribute' => 'payment_id',
                            'value' => function($model){
                                    return is_object($obPay = $model->payment) ?
                                        Yii::$app->formatter->asDate($obPay->pay_date).'('.$obPay->pay_summ.')' : 'N/A';
                                }
                        ],
                        [
                            'attribute' => 'created_at',
                            'value' => function($model){
                                    return Yii::$app->formatter->asDate($model->created_at);
                                }
                        ]
                    ]
                ])?>
            </div>
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