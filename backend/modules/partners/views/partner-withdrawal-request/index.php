<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\web\JsExpression;
/* @var $this yii\web\View */
/* @var $searchModel common\models\search\PartnerWithdrawalRequestSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app/users', 'Partner Withdrawal Requests');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class = "row">
    <div class = "col-md-12 col-sm-12 col-xs-12">
        <div class = "x_panel">
            <div class = "x_title">
                <h2><?= Html::encode($this->title) ?></h2>
                <section class="pull-right">
                    <?= Html::a(Yii::t('app/users', 'Create Partner Withdrawal Request'), ['create'], ['class' => 'btn btn-success']) ?>
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
                                        'attribute' => 'partner_id',
                                        'value' => 'partner.infoWithSite',
                                        'filter' => \kartik\select2\Select2::widget([
                                            'model' => $searchModel,
                                            'attribute' => 'partner_id',
                                            'initValueText' => $partnerDesc, // set the initial display text
                                            'options' => [
                                                'placeholder' => Yii::t('app/crm','Search for a company ...')
                                            ],
                                            'pluginOptions' => [
                                                'allowClear' => true,
                                                'minimumInputLength' => 2,
                                                'ajax' => [
                                                    'url' => \yii\helpers\Url::to(['/ajax-select/get-partners']),
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
                                        'value' => 'typeStr',
                                        'filter' => \common\models\PartnerWithdrawalRequest::getTypeMap()
                                    ],
                                    'amount:decimal',
                                    [
                                        'attribute' => 'currency_id',
                                        'value' => 'currency.name',
                                        'filter' => \common\models\ExchangeRates::getRatesCodes()
                                    ],
                                    [
                                        'attribute' => 'partner.partner_manager_id',
                                        'value' => 'partner.partnerManager.fio',
                                        'filter' => \kartik\select2\Select2::widget([
                                            'model' => $searchModel,
                                            'attribute' => 'partnerManager',
                                            'initValueText' => $pManDesc, // set the initial display text
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
                                    [
                                        'attribute' => 'manager_id',
                                        'value' => 'manager.fio',
                                        'filter' => \kartik\select2\Select2::widget([
                                            'model' => $searchModel,
                                            'attribute' => 'manager_id',
                                            'initValueText' => $managerDesc, // set the initial display text
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
                                    [
                                        'attribute' => 'date',
                                        'format' => 'date',
                                        'filter' => \kartik\date\DatePicker::widget([
                                            'model' => $searchModel,
                                            'attribute' => 'from_date',
                                            'attribute2' => 'to_date',
                                            'options' => ['placeholder' => Yii::t('app/crm','Begin date')],
                                            'options2' => ['placeholder' => Yii::t('app/crm','End date')],
                                            'type' => \kartik\date\DatePicker::TYPE_RANGE,
                                            'separator' => '-',
                                            'pluginOptions' => [
                                                //'autoclose' => true,
                                                'format' => 'dd.mm.yyyy',
                                                'defaultDate' => date('d.m.Y',time())
                                            ],
                                        ]),
                                    ],
                                    [
                                        'attribute' => 'status',
                                        'value' => 'statusStr',
                                        'filter' => \common\models\PartnerWithdrawalRequest::getStatusMap()
                                    ],
                                    [
                                        'attribute' => 'created_at',
                                        'format' => 'datetime',
                                        'filter' => \kartik\date\DatePicker::widget([
                                            'model' => $searchModel,
                                            'attribute' => 'created_at_from',
                                            'attribute2' => 'created_at_to',
                                            'options' => ['placeholder' => Yii::t('app/crm','Begin date')],
                                            'options2' => ['placeholder' => Yii::t('app/crm','End date')],
                                            'type' => \kartik\date\DatePicker::TYPE_RANGE,
                                            'separator' => '-',
                                            'pluginOptions' => [
                                                //'autoclose' => true,
                                                'format' => 'dd.mm.yyyy',
                                                'defaultDate' => date('d.m.Y',time())
                                            ],
                                        ]),
                                    ],
                                    [
                                        'class' => 'yii\grid\ActionColumn',
                                        'template' => '{view}'
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
