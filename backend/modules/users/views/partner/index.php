<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\search\PartnerSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app/users', 'Partners');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class = "row">
    <div class = "col-md-12 col-sm-12 col-xs-12">
        <div class = "x_panel">
            <div class = "x_title">
                <h2><?= Html::encode($this->title) ?></h2>
                <section class="pull-right">
                    <?= Html::a(Yii::t('app/users', 'Create Partner'), ['create'], ['class' => 'btn btn-success']) ?>
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
                            'attribute' => 'fio',
                            'label' => Yii::t('app/users','FIO'),
                            'format' => 'html',
                            'value' => function($model){
                                return Html::a($model->fio,['update','id' => $model->id],['class' => 'link-upd']);
                            }
                        ],
                        'description:ntext',
                        [
                            'attribute' => 'status',
                            'value' => function($model){
                                return $model->getStatusStr();
                            },
                            'filter' => \common\models\Partner::getStatusArr()
                        ],
                        [
                            'class' => 'common\components\customComponents\ActionColumnSettings\ActionColumnSettings',
                            'links' => [
                                [
                                    'title' => Yii::t('app/users','Partner contractor serv'),
                                    'url' => 'link-contractor-service'
                                ]
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
