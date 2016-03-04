<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\search\BillsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app/documents', 'Bills');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class = "row">
    <div class = "col-md-12 col-sm-12 col-xs-12">
        <div class = "x_panel">
            <div class = "x_title">
                <h2><?= Html::encode($this->title) ?></h2>
                <section class="pull-right">
                    <?= Html::a(Yii::t('app/documents', 'Create Bills'), ['create'], ['class' => 'btn btn-success']) ?>
                </section>
                <div class = "clearfix"></div>
            </div>
            <div class = "x_content">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    [
                        'attribute' => 'bill_number',
                        'format' => 'html',
                        'value' => function($model){
                                return Html::a($model->bill_number,['update','id' => $model->id],['class' => 'link-upd']);
                            }
                    ],
                    [
                        'attribute' => 'amount',
                        'format' => 'html',
                        'value' =>function($model){
                                return Html::a($model->amount,['update','id' => $model->id],['class' => 'link-upd']);
                            }
                    ],
                    [
                        'attribute' => 'cuser_id',
                        'format' => 'html',
                        'value' => function($model){
                                $name= is_object($obCuser = $model->cuser) ? $obCuser->getInfo() : 'N/A';
                                return Html::a($name,['update','id' => $model->id],['class' => 'link-upd']);
                            },
                        'filter' => \common\models\CUser::getContractorMap()
                    ],
                    [
                        'attribute' => 'l_person_id',
                        'format' => 'html',
                        'value' => function($model){
                                $name= is_object($obLP = $model->lPerson) ? $obLP->name : 'N/A';
                                return Html::a($name,['update','id' => $model->id],['class' => 'link-upd']);
                            },
                        'filter' => \common\models\LegalPerson::getLegalPersonMap()
                    ],
                    [
                        'attribute' => 'service_id',
                        'value' => function($model){
                                return is_object($obServ = $model->service) ? $obServ->name : 'N/A';
                            },
                        'filter' => \common\models\Services::getServicesMap()
                    ],
                    [
                        'attribute' => 'bill_date',
                        'format' => 'date',
                        'filter' => \kartik\date\DatePicker::widget([
                            'model' => $searchModel,
                            'attribute' => 'from_date',
                            'attribute2' => 'to_date',
                            'options' => ['placeholder' => Yii::t('app/crm','Begin date')],
                            'options2' => ['placeholder' => Yii::t('app/crm','End date')],
                            'type' => \kartik\date\DatePicker::TYPE_RANGE,
                            'separator' => '-',
                            'pluginOptions' => [
                                //'autoclose' => true,
                                'format' => 'dd.mm.yyyy',
                                'defaultDate' => date('d.m.Y',time())
                            ],
                        ]),
                    ],
                    [
                        'attribute' => 'docx_tmpl_id',
                        'value' => function($model){
                                return is_object($obDocx = $model->docxTmpl) ? $obDocx->name : 'N/A';
                            },
                        'filter' => \common\models\BillDocxTemplate::getBillDocxMap()
                    ],
                    [
                        'attribute' => 'external',
                        'value' => function($model){
                            return $model->getYesNoStr($model->external);
                        }
                    ],
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'template' => '{pdf}',
                        'buttons' => [
                            /*
                            'docx' => function($url, $model, $key){
                                    return Html::a('<i class="fa fa-file-word-o"></i>',[
                                        'get-bill',
                                        'type' => \common\models\Bills::TYPE_DOC_DOCX,
                                        'id' => $model->id
                                    ],
                                    [
                                        'target' => '_blank'
                                    ]);
                                },
                            */
                            'pdf' => function($url, $model, $key){
                                    return Html::a('<i class="fa fa-file-pdf-o"></i>',[
                                            'get-bill',
                                            'type' => \common\models\Bills::TYPE_DOC_PDF,
                                            'id' => $model->id
                                        ],
                                        [
                                            'target' => '_blank'
                                        ]);
                                },

                        ]
                    ],
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'template' => '{view}{plus}',
                        'buttons' => [
                            'plus' => function($url, $model, $key){
                                    return Html::a('<i class="fa fa-copy"></i>',['bill-copy','id'=>$model->id]);
                                },
                        ]
                    ],
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'template' => '{delete}'
                    ],
                ],
            ]); ?>
                <div class="col-md-4 ">
                    <?php if(!empty($iTotal)):?>
                        <?=Html::tag('h3',Yii::t('app/crm','Total').' <small>'.$iTotal.'</small>')?>
                    <?php endif;?>
                </div>
            </div>
        </div>
    </div>
</div>
