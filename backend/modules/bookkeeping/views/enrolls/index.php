<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\web\JsExpression;
/* @var $this yii\web\View */
/* @var $searchModel common\models\EnrollsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app/book', 'Enrolls');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class = "row">
    <div class = "col-md-12 col-sm-12 col-xs-12">
        <div class = "x_panel">
            <div class = "x_title">
                <h2><?= Html::encode($this->title) ?></h2>
                <section class="pull-right">
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
                                'attribute' => 'cuser_id',
                                'value' => function($model){
                                    return is_object($obCuser = $model->cuser) ? $obCuser->getInfo() : NULL;
                                },
                                'filter' => \kartik\select2\Select2::widget([
                                'model' => $searchModel,
                                'attribute' => 'cuser_id',
                                'initValueText' => $cuserDesc, // set the initial display text
                                'options' => [
                                    'placeholder' => Yii::t('app/crm','Search for a company ...')
                                ],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                    'minimumInputLength' => 2,
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
                                'attribute' => 'service_id',
                                'value' => function($model){
                                    return is_object($obServ= $model->service) ? $obServ->name.' ['.$obServ->enroll_unit.']' : NULL;
                                },
                                'filter' => \yii\helpers\ArrayHelper::map(\common\models\Services::getServiceWithAllowEnrollment(),'id','name')
                            ],
                            'amount',
                            'repay',
                            'enroll',
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'template' => '{update}{view}',
                                'visible' => Yii::$app->user->can('adminRights')
                            ],
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'template' => '{delete}',
                                'visible' => Yii::$app->user->can('adminRights')
                            ],
                        ],
                ]); ?>
                        </div>
        </div>
    </div>
</div>
