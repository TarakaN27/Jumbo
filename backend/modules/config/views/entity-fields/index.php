<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\search\EntityFieldsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app/config', 'Entity Fields');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class = "row">
    <div class = "col-md-12 col-sm-12 col-xs-12">
        <div class = "x_panel">
            <div class = "x_title">
                <h2><?= Html::encode($this->title) ?></h2>
                <section class="pull-right">
                    <?= Html::a(Yii::t('app/config', 'Create Entity Fields'), ['create'], ['class' => 'btn btn-success']) ?>
                </section>
                <div class = "clearfix"></div>
            </div>
            <div class = "x_content">
                            <?= GridView::widget([
                                'dataProvider' => $dataProvider,
                                'filterModel' => $searchModel,
                                'columns' => [
                                    ['class' => 'yii\grid\SerialColumn'],
                                    [ 'attribute' => 'name',
                                        'format' => 'html',
                                        'value' => function($model){
                                            return Html::a($model->name,['update','id' => $model->id],['class' => 'link-upd']);
                                        }
                                    ],
                                'alias',
                                [
                                    'attribute' => 'entity',
                                    'value' => function($model){
                                        return $model->getEntityStr();
                                    },
                                    'filter' => \common\models\EntityFields::getEntityArr()
                                ],
                                    [
                                        'attribute' => 'type',
                                        'value' => function($model){
                                            return $model->getTypeStr();
                                        },
                                        'filter' => \common\models\EntityFields::getTypeArr()
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
