<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\web\JsExpression;

/* @var $this yii\web\View */
/* @var $searchModel common\models\search\EnrollmentRequestSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app/book', 'Enrollment Requests');
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
                        /*
                        [
                            'attribute' => 'id',
                            'format' => 'html',
                            'value' => function($model){
                                return Html::a($model->id,['update','id' => $model->id],['class' => 'link-upd']);
                            }
                        ],
                        */
                        [
                            'attribute' => 'cuser_id',
                            'format' => 'raw',
                            'value' => function($model){
                                    $tmp =  is_object($obCuser = $model->cuser) ? $obCuser->getInfo() : NULL;
                                    if($model->status == \common\models\EnrollmentRequest::STATUS_PROCESSED)
                                        return $tmp;

                                    return Html::a($tmp,['process','id' => $model->id],['class' => 'link-upd']);
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
                                    return is_object($obServ = $model->service) ? $obServ->name : NULL;
                                },
                            'filter' => \yii\helpers\ArrayHelper::map(\common\models\Services::getServiceWithAllowEnrollment(),'id','name')
                        ],
                        'amount',
                        [
                            'attribute' => 'assigned_id',
                            'visible' => Yii::$app->user->can('adminRights'),
                            'value' => function($model){
                                    return is_object($obBuser = $model->assigned) ? $obBuser->getFio() : NULL;
                                },
                            'filter' => \kartik\select2\Select2::widget([
                                    'model' => $searchModel,
                                    'attribute' => 'assigned_id',
                                    'initValueText' => $buserDesc, // set the initial display text
                                    'options' => [
                                        'placeholder' => Yii::t('app/crm','Search for a users ...')
                                    ],
                                    'pluginOptions' => [
                                        'allowClear' => true,
                                        'minimumInputLength' => 2,
                                        'ajax' => [
                                            'url' => \yii\helpers\Url::to(['/ajax-select/get-b-user']),
                                            'dataType' => 'json',
                                            'data' => new JsExpression('function(params) { return {q:params.term}; }')
                                        ],
                                        'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                                        'templateResult' => new JsExpression('function(cmp_id) { return cmp_id.text; }'),
                                        'templateSelection' => new JsExpression('function (cmp_id) { return cmp_id.text; }'),
                                    ],
                                ])
                        ],
                        'created_at:date',


                        //'payment_id',
                        //'pr_payment_id',
                        //'service_id',
                        // 'cuser_id',
                        // 'amount',
                        // 'pay_amount',
                        // 'pay_currency',
                        // 'pay_date',
                        // 'created_at',
                        // 'updated_at',
                        /*
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{update}{view}'
                        ],
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{delete}'
                        ],
                        */
                    ],
                ]); ?>
                        </div>
        </div>
    </div>
</div>
