<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\web\JsExpression;
use yii\helpers\ArrayHelper;
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
                                $amount = is_null($model->amount) ? NULL : Yii::$app->formatter->asDecimal($model->amount);
                                return Html::a($amount,['update','id' => $model->id],['class' => 'link-upd']);
                            }
                    ],
                    [
                        'attribute' => 'cuser_id',
                        'format' => 'html',
                        'value' => function($model){
                                $name= is_object($obCuser = $model->cuser) ? $obCuser->getInfo() : 'N/A';
                                return Html::a($name,['update','id' => $model->id],['class' => 'link-upd']);
                            },
                        'filter' => \kartik\select2\Select2::widget([
                            'model' => $searchModel,
                            'attribute' => 'cuser_id',
                            'initValueText' => $cuserDesc, // set the initial display text
                            'options' => [
                                'placeholder' => Yii::t('app/crm','Search for a company ...')
                            ],
                            'pluginOptions' => [
                                'allowClear' => true,
                                'minimumInputLength' => 3,
                                'ajax' => [
                                    'url' => \yii\helpers\Url::to(['/ajax-select/get-contractor']),
                                    'dataType' => 'json',
                                    'data' => new JsExpression('function(params) { return {q:params.term}; }')
                                ],
                                'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                                'templateResult' => new JsExpression('function(cmp_id) { return cmp_id.text; }'),
                                'templateSelection' => new JsExpression('function (cmp_id) { return cmp_id.text; }'),
                            ],
                        ]),
                       // 'filter' => \common\models\CUser::getContractorMap()
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
                        'attribute' => 'bill_services',
                        'format' => 'raw',
                        'label' => Yii::t('app/documents', 'Service ID'),
                        'value' => function($model){
                            if(empty($model->service_id))
                            {
                                $arServTmp = ArrayHelper::getColumn($model->billServices,'service.name');
                                return empty($arServTmp) ? NULL : implode('<br>',$arServTmp);
                            }else{
                                return ArrayHelper::getValue($model,'service.name');
                            }
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
                                'defaultDate' => date('d.m.Y',time()),
                                'weekStart' => '1',
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
                        'template' => '{view}',
                        'buttons' => [
                            'plus' => function($url, $model, $key){
                                    return Html::a('<i class="fa fa-copy"></i>',['bill-copy','id'=>$model->id]);
                                },
                        ]
                    ],
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'visible' => !Yii::$app->user->can('only_bookkeeper'),
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
