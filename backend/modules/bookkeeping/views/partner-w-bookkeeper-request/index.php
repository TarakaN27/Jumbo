<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\grid\ActionColumn;

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
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{delete}',
                            'buttons' => [
                                'delete' => function($url, $model, $key){
                                    if($model->status == \common\models\PartnerWBookkeeperRequest::STATUS_NEW) {
                                        $options = [
                                            'title' => Yii::t('yii', 'Delete'),
                                            'aria-label' => Yii::t('yii', 'Delete'),
                                            'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
                                            'data-method' => 'post',
                                            'data-pjax' => '0',
                                        ];
                                        return Html::a('<span class="glyphicon glyphicon-trash"></span>', $url, $options);
                                    }
                                }
                            ]
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
