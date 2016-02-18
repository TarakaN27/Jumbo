<?php

use yii\helpers\Html;
use yii\grid\GridView;
use common\models\CrmTask;
use yii\web\JsExpression;

/* @var $this yii\web\View */
/* @var $searchModel common\models\search\CrmTaskSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app/crm', 'Crm Tasks');
$this->params['breadcrumbs'][] = $this->title;
if(Yii::$app->user->can('adminRights') && $viewType == \common\models\search\CrmTaskSearch::VIEW_TYPE_FULL_TASK)
{
    $columns = [
        ['class' => 'yii\grid\SerialColumn'],
        //'id',
        [
            'attribute' => 'title',
            'format' => 'html',
            'value' => function($model) use ($arNewTasks){
                $postfix = in_array($model->id,$arNewTasks) ?
                    ' <span class="label label-warning">'.Yii::t('app/crm','New').'</span>'
                    :
                    '';
                $options = ['class' => 'link-upd'];

                if($model->status == CrmTask::STATUS_CLOSE)
                {
                    $options = ['class' => 'link-upd line-through'];
                }

                return Html::a($model->title,['view','id' => $model->id],$options).$postfix;
            }
        ],
        [
            'attribute' => 'cmp_id',
            'value' => function($model){

                $obCmp = $model->cmp;
                return $obCmp ? $obCmp->getInfoWithSite() : NULL;
            },
            'filter' => \kartik\select2\Select2::widget([
                'model' => $searchModel,
                'attribute' => 'cmp_id',
                'initValueText' => $cuserDesc, // set the initial display text
                'options' => [
                    'placeholder' => Yii::t('app/crm','Search for a company ...')
                ],
                'pluginOptions' => [
                    'allowClear' => true,
                    'minimumInputLength' => 3,
                    'ajax' => [
                        'url' => \yii\helpers\Url::to(['/ajax-select/get-cmp']),
                        'dataType' => 'json',
                        'data' => new JsExpression('function(params) { return {q:params.term}; }')
                    ],
                    'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                    'templateResult' => new JsExpression('function(cmp_id) { return cmp_id.text; }'),
                    'templateSelection' => new JsExpression('function (cmp_id) { return cmp_id.text; }'),
                ],
            ])
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
            'format' => 'raw',
            'filter' =>  \yii\jui\DatePicker::widget([
                'model'=>$searchModel,
                'attribute'=>'deadline',
                'language' => 'ru',
                'dateFormat' => 'yyyy-MM-dd',
                'options' =>['class' => 'form-control'],
                'clientOptions' => [
                    'defaultDate' => date('y-m-d',time())
                ],
            ]),
            'value' => function($model){
                $options = [];
                if(!empty($model->deadline))
                {
                    if(!in_array($model->status,[CrmTask::STATUS_NEED_ACCEPT,CrmTask::STATUS_CLOSE]))
                    {
                        $time = strtotime($model->deadline);
                        $timeNow = time();
                        if($time < $timeNow)
                            $options = [
                                'class' => 'red'
                            ];
                        elseif($time < time()+4*3600)
                            $options = [
                                'class' => 'yellow'
                            ];
                    }
                }else{
                    return NULL;
                }
                return Html::tag('span',$model->deadline,$options);
            }
        ],
        [
            'attribute' => 'priority',
            'value' => function($model){
                return $model->getPriorityStr();
            },
            'filter' => \common\models\CrmTask::getPriorityArr()
        ],
        [
            'attribute' => 'status',
            'format' => 'raw',
            'value' => function($model){
                return $model->getStatusStr();
            },
            'filter' => \kartik\select2\Select2::widget([
                'model' => $searchModel,
                'attribute' => 'status',
                'data' => \common\models\CrmTask::getStatusArr(),
                'options' => [
                    'multiple' => true
                ],
            ])
        ],
        [
            'attribute' => 'assigned_id',
            'value' => function($model){

                return is_object($obUser = $model->assigned) ? $obUser->getFio() : $model->assigned_id;
            },
            'filter' => \backend\models\BUser::getAllMembersMap()
        ],
        [
            'class' => 'yii\grid\ActionColumn',
            'template' => '{update}{view}',
            'buttons' => [
                'update' => function ($url, $model, $key) {

                    if($model->created_by != Yii::$app->user->id)
                        return NULL;

                    $options = [
                        'title' => Yii::t('yii', 'Update'),
                        'aria-label' => Yii::t('yii', 'Update'),
                        'data-pjax' => '0',
                    ];
                    return Html::a('<span class="glyphicon glyphicon-pencil"></span>', $url, $options);
                }
            ]
        ],
        [
            'class' => 'yii\grid\ActionColumn',
            'template' => '{delete}',
            'buttons' => [
                'delete' => function ($url, $model, $key) {
                    if($model->created_by != Yii::$app->user->id)
                        return NULL;
                    $options = [
                        'title' => Yii::t('yii', 'Delete'),
                        'aria-label' => Yii::t('yii', 'Delete'),
                        'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
                        'data-method' => 'post',
                        'data-pjax' => '0',
                    ];
                    return Html::a('<span class="glyphicon glyphicon-trash"></span>', $url, $options);
                }
            ]
        ],
    ];
}else{
    $columns = [
        ['class' => 'yii\grid\SerialColumn'],
        [
            'attribute' => 'title',
            'format' => 'html',
            'value' => function($model) use ($arNewTasks){

                $postfix = in_array($model->id,$arNewTasks) ?
                    ' <span class="label label-warning">'.Yii::t('app/crm','New').'</span>'
                    :
                    '';
                $options = ['class' => 'link-upd'];

                if($model->status == CrmTask::STATUS_CLOSE)
                {
                    $options = ['class' => 'link-upd line-through'];
                }
                return Html::a($model->title,['view','id' => $model->id],$options).$postfix;
            }
        ],
        [
            'attribute' => 'cmp_id',
            'value' => function($model){

                $obCmp = $model->cmp;
                return $obCmp ? $obCmp->getInfoWithSite() : NULL;
            },
            'filter' => \kartik\select2\Select2::widget([
                'model' => $searchModel,
                'attribute' => 'cmp_id',
                'initValueText' => $cuserDesc, // set the initial display text
                'options' => [
                    'placeholder' => Yii::t('app/crm','Search for a company ...')
                ],
                'pluginOptions' => [
                    'allowClear' => true,
                    'minimumInputLength' => 3,
                    'ajax' => [
                        'url' => \yii\helpers\Url::to(['/ajax-select/get-cmp']),
                        'dataType' => 'json',
                        'data' => new JsExpression('function(params) { return {q:params.term}; }')
                    ],
                    'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                    'templateResult' => new JsExpression('function(cmp_id) { return cmp_id.text; }'),
                    'templateSelection' => new JsExpression('function (cmp_id) { return cmp_id.text; }'),
                ],
            ])
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
            'format' => 'raw',
            'filter' =>  \yii\jui\DatePicker::widget([
                'model'=>$searchModel,
                'attribute'=>'deadline',
                'language' => 'ru',
                'dateFormat' => 'yyyy-MM-dd',
                'options' =>['class' => 'form-control'],
                'clientOptions' => [
                    'defaultDate' => date('y-m-d',time())
                ],
            ]),
            'value' => function($model){
                $options = [];
                if(!empty($model->deadline))
                {
                    if(!in_array($model->status,[CrmTask::STATUS_NEED_ACCEPT,CrmTask::STATUS_CLOSE]))
                    {
                        $time = strtotime($model->deadline);
                        $timeNow = time();
                        if($time < $timeNow)
                            $options = [
                                'class' => 'red'
                            ];
                        elseif($time < time()+4*3600)
                            $options = [
                                'class' => 'yellow'
                            ];
                    }
                }else{
                    return NULL;
                }
                return Html::tag('span',$model->deadline,$options);
            }
        ],
        [
            'attribute' => 'priority',
            'value' => function($model){
                return $model->getPriorityStr();
            },
            'filter' => \common\models\CrmTask::getPriorityArr()
        ],
        [
            'attribute' => 'status',
            'format' => 'raw',
            'value' => function($model){
                return $model->getStatusStr();
            },
            'filter' => \kartik\select2\Select2::widget([
                'model' => $searchModel,
                'attribute' => 'status',
                'data' => \common\models\CrmTask::getStatusArr(),
                'options' => [
                    'multiple' => true
                ],
            ])

        ],
        [
            'class' => 'yii\grid\ActionColumn',
            'template' => '{update}{view}',
            'buttons' => [
                'update' => function ($url, $model, $key) {

                    if($model->created_by != Yii::$app->user->id)
                        return NULL;

                    $options = [
                        'title' => Yii::t('yii', 'Update'),
                        'aria-label' => Yii::t('yii', 'Update'),
                        'data-pjax' => '0',
                    ];
                    return Html::a('<span class="glyphicon glyphicon-pencil"></span>', $url, $options);
                }
            ]
        ],
        [
            'class' => 'yii\grid\ActionColumn',
            'template' => '{delete}',
            'buttons' => [
                'delete' => function ($url, $model, $key) {
                    if($model->created_by != Yii::$app->user->id)
                        return NULL;
                    $options = [
                        'title' => Yii::t('yii', 'Delete'),
                        'aria-label' => Yii::t('yii', 'Delete'),
                        'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
                        'data-method' => 'post',
                        'data-pjax' => '0',
                    ];
                    return Html::a('<span class="glyphicon glyphicon-trash"></span>', $url, $options);
                }
            ]
        ],
    ];
}

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
                    'columns' => $columns
                ]); ?>
            </div>
        </div>
    </div>
</div>
