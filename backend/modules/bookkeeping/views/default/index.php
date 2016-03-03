<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\web\JsExpression;
/* @var $this yii\web\View */
/* @var $searchModel common\models\search\PaymentsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app/book', 'Payments');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class = "row">
    <div class = "col-md-12 col-sm-12 col-xs-12">
        <div class = "x_panel">
            <div class = "x_title">
                <h2><?php echo $this->title?></h2>
                <section class="pull-right">
                    <?php if(Yii::$app->user->can('adminRights') || Yii::$app->user->can('only_bookkeeper')):?>
                        <?= Html::a(Yii::t('app/book', 'Create payment request'), ['create-payment-request'], ['class' => 'btn btn-success']) ?>
                    <?php endif;?>
                </section>
                <div class = "clearfix"></div>
            </div>
            <div class = "x_content">
                <?
                $tpl = '';
                $viewTpl = '';
                    if(Yii::$app->user->can('adminRights') || Yii::$app->user->can('only_bookkeeper'))
                    {
                        $tpl = '{delete}';
                        $viewTpl = '{view}';
                    }
                    elseif(Yii::$app->user->can('only_manager'))
                    {
                        $viewTpl = '{view}';
                    }
                    echo \common\components\widgets\WMCPageSize\WMCPageSize::widget();
                    echo GridView::widget([
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
                        'filterSelector' => 'select[name="per-page"]',
                        'tableOptions' => ['class' => 'table table-striped table-bordered table-responsive'],
                        'columns' => [
                            ['class' => 'yii\grid\SerialColumn'],
                            [
                                'attribute' => 'cuser_id',
                                'format' => 'html',
                                'value' => function($model){
                                        $name = is_object($cuser = $model->cuser) ? $cuser->getInfo() : 'N/A';
                                        if(
                                            Yii::$app->user->can('adminRights') ||
                                            Yii::$app->user->can('only_bookkeeper') ||
                                            Yii::$app->user->can('only_manager')
                                        )
                                            return Html::a($name,['update','id'=>$model->id],['class'=>'link-upd']);
                                        else
                                            return $name;
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
                                        'minimumInputLength' => 3,
                                        'ajax' => [
                                            'url' => \yii\helpers\Url::to(['/ajax-select/get-contractor']),
                                            'dataType' => 'json',
                                            'data' => new JsExpression('function(params) { return {q:params.term}; }')
                                        ],
                                        'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                                        'templateResult' => new JsExpression('function(cmp_id) { return cmp_id.text; }'),
                                        'templateSelection' => new JsExpression('function (cmp_id) { return cmp_id.text; }'),
                                    ],
                                ]),
                            ],
                            [
                                'attribute' => 'service_id',
                                'value' => function($model){
                                        return is_object($service = $model->service) ? $service->name : 'N/A';
                                    },
                                'filter' => \common\models\Services::getServicesMap()
                            ],
                            [
                                'attribute' => 'legal_id',
                                'value' => function($model){
                                        return is_object($legal = $model->legal) ? $legal->name : 'N/A';
                                    },
                                'filter' => \common\models\LegalPerson::getLegalPersonMap()
                            ],
                            'payment_order',
                            [
                                'attribute' => 'pay_summ',
                                'format'=>['decimal',Yii::$app->params['decimalRound']]
                            ],
                            [
                                'attribute' => 'currency_id',
                                'value' => function($model){
                                        return is_object($cur = $model->currency) ? $cur->code : 'N/A';
                                    },
                                'filter' => \common\models\ExchangeRates::getRatesCodes()
                            ],
                            [
                                'attribute' => 'pay_date',
                                'filter' => \yii\jui\DatePicker::widget([

                                        'model'=>$searchModel,
                                        'attribute'=>'pay_date',
                                        'language' => 'ru',
                                        'dateFormat' => 'dd.MM.yyyy',
                                        'options' =>['class' => 'form-control'],
                                        'clientOptions' => [
                                            'defaultDate' => date('d.m.Y',time())
                                        ],
                                    ]),
                                'format' => 'date',
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
