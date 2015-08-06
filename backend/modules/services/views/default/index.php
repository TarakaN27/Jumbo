<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\search\ServicesSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app/services', 'Services');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class = "row">

<div class = "col-md-12 col-sm-12 col-xs-12">
                            <div class = "x_panel">
                                <div class = "x_title">
                                    <h2><?php echo $this->title?></h2>
                                    <section class="pull-right">
                                    <?php if(Yii::$app->user->can('adminRights')):?>
                                        <?= Html::a(Yii::t('app/services', 'Create Services'), ['create'], ['class' => 'btn btn-success']) ?>
                                    <?php endif;?>
                                    </section>
                                    <div class = "clearfix"></div>
                                </div>
                                <div class = "x_content">
    <?php
        $tpl = '';
        $viewTpl = '';
        if(Yii::$app->user->can('adminRights'))
        {
            $tpl = '{delete}';
            $viewTpl = '{view}';
        }
        elseif(Yii::$app->user->can('only_manager'))
            $viewTpl = '{view}';

        echo GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                [
                    'attribute' => 'name',
                    'format' => 'html',
                    'value' => function($model)
                        {
                            if(Yii::$app->user->can('adminRights'))
                                return Html::a($model->name,['update','id'=>$model->id],['class'=>'link-upd']);
                            else
                                $model->name;
                        }
                ],
                'description',
                [
                    'attribute' => 'status',
                    'value' => function($model){
                            return $model->getStatusStr();
                        },
                    'filter' => \common\models\Services::getStatusArr()
                ],
                [
                    'attribute' => 'created_at',
                    'value' => function($model){
                            return $model->getFormatedCreatedAt();
                        }
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template'=>$viewTpl
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template'=> $tpl
                ]
            ]
        ]); ?>

                                </div>
                            </div>
                        </div>
</div>
