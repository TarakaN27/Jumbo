<?php

use yii\helpers\Html;
use yii\grid\GridView;
use common\models\Services;
use common\models\LegalPerson;
/* @var $this yii\web\View */
/* @var $searchModel common\models\search\ActFieldTemplateSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app/book', 'Act Field Templates');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class = "row">
    <div class = "col-md-12 col-sm-12 col-xs-12">
        <div class = "x_panel">
            <div class = "x_title">
                <h2><?= Html::encode($this->title) ?></h2>
                <section class="pull-right">
                    <?= Html::a(Yii::t('app/book', 'Create Act Field Template'), ['create'], ['class' => 'btn btn-success']) ?>
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
                        [
                            'attribute' => 'service_id',
                            'value' => 'service.name',
                            'filter' => Services::getServicesMap()
                        ],
                        [
                            'attribute' => 'legal_id',
                            'value' => 'legal.name',
                            'filter' => LegalPerson::getLegalPersonMap()
                        ],
                        //'job_name:ntext',
                        'created_at:datetime',
                        // 'updated_at',
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
