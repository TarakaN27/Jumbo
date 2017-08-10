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
                <?php echo \common\components\widgets\WMCPageSize\WMCPageSize::widget();?>
                            <?= GridView::widget([
                                'dataProvider' => $dataProvider,
                                'filterModel' => $searchModel,
                                'filterSelector' => 'select[name="per-page"]',
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
                                            return is_object($obServ= $model->service) ? $obServ->name : NULL;
                                        },
                                        'filter' => \yii\helpers\ArrayHelper::map(\common\models\Services::getServiceWithAllowEnrollment(),'id','name')
                                    ],
                                    [
                                        'attribute' => 'amount',
                                        'format' => 'decimal',
                                        'label' => Yii::t('app/book','Counting unit amount')
                                    ],
                                    'repay:decimal',
                                    'enroll:decimal',
                                    [
                                        'attribute' => 'enroll_unit_id',
                                        'value' => 'unitEnroll.name',
                                        'filter' => \common\models\UnitsEnroll::getUnitsEnrollsDropDown()
                                    ],
                                    [
                                        'attribute' => 'payName',
                                        'value' => 'payName',
                                        'filter' => \common\models\PaymentCondition::getAllConditionArray()
                                    ],
                                    [
                                        'attribute' => 'rateName',
                                        'value' => 'rateName',
                                        'filter' => \common\models\ExchangeRates::getAllRatesArray()
                                    ],
                                    [
                                        'attribute' => 'rate_nbrb',
                                        'value' =>function($model){
                                            return number_format($model->rate_nbrb,5,',','');
                                        },
                                    ],
                                    [
                                        'attribute' => 'created_at',
                                        'format' => 'datetime',
                                        'label' => Yii::t('app/book','Processing date'),
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
                                        'class' => 'yii\grid\ActionColumn',
                                        'template' => '{update}',
                                        'visible' => Yii::$app->user->can('adminRights')
                                    ],
                                    [
                                        'content' => function ($model) {
                                            if ($model->b_user_enroll == Yii::$app->user->id || Yii::$app->user->can('adminRights')) {
                                                return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', ['view', 'id' => $model->id]);
                                            }else return false;
                                        },
                                    ],
                                    [
                                        'class' => 'yii\grid\ActionColumn',
                                        'template' => '{delete}',
                                        'visible' => Yii::$app->user->can('adminRights')
                                    ],
                                ],
                        ]); ?>

                <div class="col-md-4 col-md-offset-8">
                    <?php if(!empty($arTotal)):?>
                        <?=Html::tag('h3',Yii::t('app/crm','Total'))?>
                        <table class="table table-striped table-bordered">
                            <?php foreach($arTotal as $value):?>
                                <tr>
                                    <th><?=$value['nameServiceWithUnitEnroll'];?></th>
                                    <td><?=Yii::$app->formatter->asDecimal($value['amount']);?></td>
                                </tr>
                            <?php endforeach;?>
                        </table>
                    <?php endif;?>
                </div>
            </div>
        </div>
    </div>
</div>
