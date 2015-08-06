<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\search\LegalPersonSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app/services', 'Legal People');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class = "row">
    <div class = "col-md-12 col-sm-12 col-xs-12">
        <div class = "x_panel">
            <div class = "x_title">
                <h2><?php echo $this->title?></h2>
                <section class="pull-right">
                <?php if(Yii::$app->user->can('only_bookkeeper') || Yii::$app->user->can('superRights')):?>
                    <?= Html::a(Yii::t('app/services', 'Create Legal Person'), ['create'], ['class' => 'btn btn-success']) ?>
                <?php endif;?>
                </section>
                <div class = "clearfix"></div>
            </div>
            <div class = "x_content">
                <?php

                    $tpl = '';
                    $viewTpl = '';
                    if(Yii::$app->user->can('superRights') || Yii::$app->user->can('only_bookkeeper'))
                    {
                        $tpl = '{delete}';
                        $viewTpl = '{view}';
                    }
                    elseif(Yii::$app->user->can('only_manager') || Yii::$app->user->can('adminRights'))
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
                                        if(Yii::$app->user->can('adminRights') ||Yii::$app->user->can('only_bookkeeper'))
                                            return Html::a($model->name,['update','id'=>$model->id],['class'=>'link-upd']);
                                        else
                                            $model->name;
                                    }
                            ],
                            'description:ntext',
                            [
                                'attribute' => 'status',
                                'value' => function($model){
                                        return $model->getStatusStr();
                                    },
                                'filter' => \common\models\LegalPerson::getStatusArr()
                            ],
                            [
                                'attribute' => 'created_at',
                                'value' => function($model){
                                        return $model->getFormatedCreatedAt();
                                    }
                            ],
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'template' => $viewTpl
                            ],
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'template' => $tpl
                            ],
                        ],
                    ]); ?>

            </div>
        </div>
    </div>
</div>
