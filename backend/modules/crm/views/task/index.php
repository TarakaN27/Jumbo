<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\search\CrmTaskSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app/crm', 'Crm Tasks');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class = "row">
    <div class = "col-md-12 col-sm-12 col-xs-12">
        <div class = "x_panel">
            <div class = "x_title">
                <h2 class="crm-task-title"><?= Html::encode($this->title) ?></h2>
                <section class="block-view-type">
                    <?php
                        $arViewTypes = \common\models\search\CrmTaskSearch::getViewTypeArr();
                        foreach($arViewTypes as $key => $type)
                        {
                            if($viewType == $key)
                                echo Html::a($type,NULL,['class' => 'btn btn-info']);
                            else
                                echo Html::a($type,['index','viewType' => $key],['class' => 'btn btn-default']);
                        }
                    ?>
                </section>
                <section class="pull-right">
                    <?= Html::a(Yii::t('app/crm', 'Create Crm Task'), ['create'], ['class' => 'btn btn-success']) ?>
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
                            'attribute' => 'title',
                            'format' => 'html',
                            'value' => function($model){
                                return Html::a($model->title,['view','id' => $model->id],['class' => 'link-upd']);
                            }
                        ],
                        [
                            'attribute' => 'type',
                            'value' => function($model){
                                return $model->getTypeStr();
                            },
                            'filter' => \common\models\CrmTask::getTypeArr()
                        ],

                        [
                            'attribute' => 'deadline',
                        ],
                        [
                            'attribute' => 'priority',
                            'value' => function($model){
                                return $model->getPriorityStr();
                            },
                            'filter' => \common\models\CrmTask::getPriorityArr()
                        ],
                        //'priority',
                        // 'type',
                        // 'task_control',
                        // 'parent_id',
                        // 'assigned_id',
                        // 'created_by',
                        // 'time_estimate:datetime',
                        // 'status',
                        // 'date_start',
                        // 'duration_fact',
                        // 'closed_by',
                        // 'closed_date',
                        // 'cmp_id',
                        // 'contact_id',
                        // 'dialog_id',
                        // 'created_at',
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
