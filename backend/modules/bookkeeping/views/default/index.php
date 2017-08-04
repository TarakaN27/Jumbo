<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\web\JsExpression;

use common\components\helpers\CustomViewHelper;
/* @var $this yii\web\View */
/* @var $searchModel common\models\search\PaymentsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app/book', 'Payments');
$this->params['breadcrumbs'][] = $this->title;

CustomViewHelper::registerJsFileWithDependency('@web/js/parts/payments_index.js',$this);
?>
<div class = "row">
    <div class = "col-md-12 col-sm-12 col-xs-12">
        <div class = "x_panel">
            <div class = "x_title">
                <h2><?php echo $this->title?></h2>
                <section class="pull-right">
                    <?php if(Yii::$app->user->can('superRights') || Yii::$app->user->can('adminRights') || Yii::$app->user->can('only_bookkeeper')):?>
                        <?= Html::a(Yii::t('app/book', 'Load payments'), ['load-xml'], ['class' => 'btn btn-danger']) ?>
                    <?php endif;?>
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
                $updateTpl = '';
                    if(Yii::$app->user->can('adminRights') || Yii::$app->user->can('only_bookkeeper'))
                    {
                        $tpl = '{delete}';
                        $viewTpl = '{view}';
                        $updateTpl = '{update}';
                    }
                    elseif(Yii::$app->user->can('only_manager'))
                    {
                        $viewTpl = '{view}';
                        $updateTpl = '{update}';
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
                                'attribute' => 'manager',
                                'value' => 'cuser.manager.fio',
                                'label' => Yii::t('app/book','Responsibility'),
                                'filter' => \kartik\select2\Select2::widget([
                                    'model' => $searchModel,
                                    'attribute' => 'manager',
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
                            [
                                'attribute' => 'bank_id',
                                'label' => Yii::t('app/book', 'Bank'),
                                'value' => 'payRequest.bankDetails.name',
                                'filter' => \common\models\BankDetails::getActiveBankDetails()
                            ],
                            'payment_order',
                            [
                                'attribute' => 'pay_summ',
                                'format'=>['decimal',Yii::$app->params['decimalRound']]
                            ],
                            [
                                'label' => Yii::t('app/book','Payments acts amount'),
                                'format' => 'raw',
                                'value' => function($model) use ($arActs){
                                    if($model->act_close)
                                        $amount = 0;
                                    else {
                                        $amount = isset($arActs[$model->id]) ?
                                            ((float)$model->pay_summ - (float)$arActs[$model->id]) :
                                            $model->pay_summ;
                                    }

                                    return Html::tag(
                                        'span',
                                        Yii::$app->formatter->asDecimal($amount),
                                        [
                                            'class' => $amount > 0 ? 'yellow' : 'green'
                                        ]
                                        );
                                }
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
                                'attribute' => 'act_close',
                                'format' => 'boolean',
                                'filter' => \common\models\Payments::getYesNo()
                            ],
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'template' => $viewTpl
                            ],
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'template' => $updateTpl
                            ],
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'buttons'=>[
                                    'delete'=>function ($url, $model) {
                                        return "<span data-id='$model->id' class='glyphicon glyphicon-trash payDelete' style='cursor:pointer;'></span>";
                                    }
                                ],
                                'template' =>  $tpl
                            ],
                        ],
                    ]); ?>
                <div class="col-md-4 col-md-offset-8">
                    <?php if(!empty($arTotal)):?>
                    <?=Html::tag('h3',Yii::t('app/crm','Total'))?>
                        <table class="table table-striped table-bordered">
                            <?php foreach($arTotal as $key => $value):?>
                                <tr>
                                    <th><?=$key;?></th>
                                    <td><?=$value;?></td>
                                </tr>
                            <?php endforeach;?>
                        </table>
                    <?php endif;?>
                </div>
            </div>
        </div>
    </div>
</div>
