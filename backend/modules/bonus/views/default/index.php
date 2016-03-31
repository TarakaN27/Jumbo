<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\search\BonusSchemeSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app/bonus', 'Bonus Schemes');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class = "row">
    <div class = "col-md-12 col-sm-12 col-xs-12">
        <div class = "x_panel">
            <div class = "x_title">
                <h2><?= Html::encode($this->title) ?></h2>
                <section class="pull-right">
                    <?= Html::a(Yii::t('app/bonus', 'Create Bonus Scheme'), ['create'], ['class' => 'btn btn-success']) ?>
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
                                return Html::a($model->id,['update','id' => $model->id],['class' => 'link-upd']);
                            }
                        ],
                        'name',
                        [
                            'attribute' => 'type',
                            'value' => function($model){
                                return $model->getTypeStr();
                            },
                            'filter' => \common\models\BonusScheme::getTypeMap()
                        ],
                        'num_month',
                        [
                            'attribute' => 'grouping_type',
                            'value' => function($model){
                                return $model->getGroupingTypeStr();
                            },
                            'filter' => \common\models\BonusScheme::getGroupByMap()
                        ],
                        [
                            'label' => '',
                            'format' => 'raw',
                            'contentOptions' => ['class' => 'text-center'],
                            'headerOptions' => ['class' => 'text-center'],

                            'value' => function($model){
                                return '
                                <div class="btn-group">
                                     <a data-toggle="dropdown" class="dropdown-toggle link-btn-cursor" type="button" aria-expanded="false">
                                         <i class="glyphicon glyphicon-cog"></i>
                                     </a>
                                     <ul class="dropdown-menu">
                                         <li>
                                             '.Html::a(Yii::t('app/bonus','Connect users'),['connect-user','id' => $model->id]).'
                                         </li>
                                         <li>
                                             '.Html::a(Yii::t('app/bonus','Connect cusers'),['connect-cuser','id' => $model->id]).'
                                         </li>
                                         <li>
                                             '.Html::a(Yii::t('app/bonus','Except cusers'),['except-user','id' => $model->id]).'
                                         </li>
                                     </ul>
                                </div>
                                                            ';
                            }
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
