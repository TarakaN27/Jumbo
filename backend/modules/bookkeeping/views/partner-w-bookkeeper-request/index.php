<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\search\PartnerWBookkeeperRequestSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app/book', 'Partner Wbookkeeper Requests');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class = "row">
    <div class = "col-md-12 col-sm-12 col-xs-12">
        <div class = "x_panel">
            <div class = "x_title">
                <h2><?= Html::encode($this->title) ?></h2>
                <section class="pull-right">

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
                            'attribute' => 'id',
                            'format' => 'html',
                            'value' => function($model){
                                return $model->id;
                            }
                        ],
                        [
                            'attribute' => 'buser_id',
                            'value' => 'buser.fio',
                            'visible' => Yii::$app->user->can('adminRights')
                        ],
                        [
                            'attribute' => 'partner_id',
                            'value' => 'partner.infoWithSite'
                        ],
                        [
                            'attribute' => 'contractor_id',
                            'value' => 'contractor.infoWithSite'
                        ],
                        'amount:decimal',
                        [
                            'attribute' => 'currency_id',
                            'value' => 'currency.code',
                            'filter' => \common\models\ExchangeRates::getRatesCodes()
                        ],
                        [
                            'attribute' => 'legal_id',
                            'value' => 'legal.name',
                            'filter' => \common\models\LegalPerson::getLegalPersonMap()
                        ],
                        [
                            'attribute' => 'status',
                            'value' => 'statusStr',
                            'filter' => \common\models\PartnerWBookkeeperRequest::getStatusMap()
                        ],
                        'created_at:datetime',
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{pdf}',
                            'buttons' => [
                                'pdf' => function($url, $model, $key){
                                    return Html::a('<i class="fa fa-file-pdf-o"></i>',$url,[
                                        'target' => '_blank'
                                    ]);
                                }
                            ]
                        ],
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{process}',
                            'buttons' => [
                                'process' => function($url, $model, $key){
                                    
                                    if($model->status != \common\models\PartnerWBookkeeperRequest::STATUS_NEW)
                                        return NULL;
                                    
                                    return Html::a('<i class="fa fa-credit-card"></i>',$url);
                                }
                            ]
                        ],
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{view}'
                        ],
                        /*
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{delete}',
                        ],
                        */
                    ],
                ]); ?>
            </div>
        </div>
    </div>
</div>
