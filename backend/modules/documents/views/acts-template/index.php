<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\search\ActsTemplateSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app/documents', 'Acts Templates');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class = "row">
    <div class = "col-md-12 col-sm-12 col-xs-12">
        <div class = "x_panel">
            <div class = "x_title">
                <h2><?= Html::encode($this->title) ?></h2>
                <section class="pull-right">
                    <?= Html::a(Yii::t('app/documents', 'Create Acts Template'), ['create'], ['class' => 'btn btn-success']) ?>
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
                        'attribute' => 'name',
                        'format' => 'html',
                        'value' => function($model){
                            return Html::a($model->name,['update','id' => $model->id],['class' => 'link-upd']);
                            } ],
                    [
                        'attribute' => 'is_default',
                        'filter' => \common\models\ActsTemplate::getYesNo(),
                        'value' => function($model){
                            return $model->getYesNoStr($model->is_default);
                        }
                    ],
                    [
                        'attribute' => 'created_at',
                        'value' => function($model){
                            return Yii::$app->formatter->asDatetime($model->created_at);
                        }
                    ],
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'template' => '{update}{view}{plus}',
                        'buttons' => [
                            'plus' => function($url, $model, $key){
                                return Html::a(
                                    '<i class="fa fa-download"></i>',
                                    ['download-file','id'=>$model->id],
                                    ['target' => '_blank']
                                    );
                            },
                        ]
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
