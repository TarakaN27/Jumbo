<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\search\ActsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app/book', 'Acts');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class = "row">
    <div class = "col-md-12 col-sm-12 col-xs-12">
        <div class = "x_panel">
            <div class = "x_title">
                <h2><?= Html::encode($this->title) ?></h2>
                <section class="pull-right">
                    <?= Html::a(Yii::t('app/book', 'Create Acts'), ['create'], ['class' => 'btn btn-success']) ?>
                </section>
                <div class = "clearfix"></div>
            </div>
            <div class = "x_content">
                            <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],
                        ['class' => 'yii\grid\CheckboxColumn'],
                        [
                            'attribute' => 'act_num',
                            'format' => 'html',
                            'value' => function($model){
                                return Html::a($model->act_num,['update','id' => $model->id],['class' => 'link-upd']);
                            }
                        ],
                        [
                            'attribute' => 'amount',
                            'format' => 'html',
                            'value' => function($model){
                                                return Html::a($model->amount,['update','id' => $model->id],['class' => 'link-upd']);
                            }
                        ],
                        [
                            'attribute' => 'cuser_id',
                            'value' => function($model){
                                return is_object($obCuser = $model->cuser) ? $obCuser->getInfo() : $model->cuser_id;
                            },
                            'filter' => \common\models\CUser::getContractorMap()
                        ],
                        [
                            'attribute' => 'service_id',
                            'value' => function($model){
                                return is_object($obServ = $model->service) ? $obServ->name : $model->service_id;
                            },
                            'filter' => \common\models\Services::getServicesMap()
                        ],
                        'act_date',
                        [
                            'attribute' => 'sent',
                            'value' => function($model){
                                return $model->getYesNoStr($model->sent);
                            },
                            'filter' => \common\models\Acts::getYesNo()
                        ],

                        [
                            'attribute' => 'buser_id',
                            'value' => function($model){
                                return is_object($obBuser = $model->buser) ? $obBuser->getFio() : $model->buser_id;
                            },
                            'filter' => \backend\models\BUser::getAllMembersMap()
                        ],
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{dwld}',
                            'buttons' => [
                                'dwld' => function($url, $model, $key){
                                    $options = [
                                        'title' => Yii::t('app/common', 'Download'),
                                        'aria-label' => Yii::t('app/common', 'Download'),
                                        'target' => '_blank',
                                    ];
                                    $url = \yii\helpers\Url::to(['download-file','ask' => $model->ask]);
                                    return Html::a('<span class="glyphicon glyphicon-download-alt"></span>', $url, $options);
                                }
                            ]
                        ],
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{update}{view}'
                        ],
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{delete}'
                        ],
                    ],
                ]); ?>
                        </div>
        </div>
    </div>
</div>
