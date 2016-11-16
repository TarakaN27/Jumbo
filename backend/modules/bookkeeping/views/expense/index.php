<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\web\JsExpression;
/* @var $this yii\web\View */
/* @var $searchModel common\models\search\ExpenseSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app/book', 'Expenses');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class = "row">
<div class = "col-md-12 col-sm-12 col-xs-12">
                            <div class = "x_panel">
                                <div class = "x_title">
                                    <h2><?php echo $this->title?></h2>
                                    <section class="pull-right">
                                        <?php if(Yii::$app->user->can('superRights') || Yii::$app->user->can('only_bookkeeper')):?>
                                            <?= Html::a(Yii::t('app/book', 'Migrate Expense'), ['migrate-1c'], ['class' => 'btn btn-danger']) ?>
                                        <?php endif;?>

                                        <?php if(Yii::$app->user->can('superRights') || Yii::$app->user->can('only_bookkeeper')):?>
                                            <?= Html::a(Yii::t('app/book', 'Create Expense'), ['create'], ['class' => 'btn btn-success']) ?>
                                        <?php endif;?>
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
                                            'format' => 'html',
                                            'value' => function($model){
                                                    $name =  is_object($cuser = $model->cuser) ? $cuser->getInfo() : 'N/A';
                                                    if(Yii::$app->user->can('superRights') || Yii::$app->user->can('only_bookkeeper'))
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
                                                    'minimumInputLength' => 2,
                                                    'ajax' => [
                                                        'url' => \yii\helpers\Url::to(['/ajax-select/get-expense-user']),
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
                                            'attribute' => 'cat_id',
                                            'value' => function($model){
                                                    return is_object($cat = $model->cat) ? $cat->name : 'N/A';
                                                },
                                            'filter' => \common\models\ExpenseCategories::getExpenseCatMap()
                                        ],

                                        'pay_summ:decimal',
                                        [
                                            'attribute' => 'currency_id',
                                            'value' => function($model){
                                                    return is_object($curr = $model->currency) ? $curr->code : 'N/A';
                                                },
                                            'filter' => \common\models\ExchangeRates::getRatesCodes()
                                        ],
                                        [
                                            'attribute' => 'legal_id',
                                            'value' => function($model){
                                                    return is_object($legal = $model->legal) ? $legal->name : 'N/A';
                                                },
                                            'filter' => \common\models\LegalPerson::getLegalPersonMapWithRoleControl()
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
                                            'class' => 'yii\grid\ActionColumn',
                                            'template' => '{view}',
                                            'visible' => Yii::$app->user->can('superRights') || Yii::$app->user->can('only_bookkeeper')
                                        ],
                                        [
                                            'class' => 'yii\grid\ActionColumn',
                                            'template' => '{delete}',
                                            'visible' => Yii::$app->user->can('superRights')
                                        ],
                                    ],
                                ]); ?>
                                    <div class="col-md-4 col-md-offset-8">
                                        <?php if(!empty($arTotal) && !empty($arTotal['total'])):?>
                                            <?=Html::tag('h3',Yii::t('app/crm','Total'))?>
                                            <table class="table table-bordered">
                                                <?php foreach($arTotal['total'] as $key => $value):?>
                                                    <tr>
                                                        <th><?=$key;?></th>
                                                        <td><?=Yii::$app->formatter->asDecimal($value);?></td>
                                                    </tr>
                                                <?php endforeach;?>
                                                <tr class="incomingPurse">
                                                    <th><?=Yii::t('app/book','Total in BYR')?></th>
                                                    <td><?=Yii::$app->formatter->asDecimal($arTotal['totalByr']);?></td>
                                                </tr>
                                            </table>
                                        <?php endif;?>
                                        <?php if(!empty($arTotal) && !empty($arTotal['withoutIgnore'])):?>
                                            <?=Html::tag('h3',Yii::t('app/crm','Total without ignore'))?>
                                            <table class="table table-bordered">
                                                <?php foreach($arTotal['withoutIgnore'] as $key => $value):?>
                                                    <tr>
                                                        <th><?=$key;?></th>
                                                        <td><?=Yii::$app->formatter->asDecimal($value);?></td>
                                                    </tr>
                                                <?php endforeach;?>
                                                <tr class="incomingPurse">
                                                    <th><?=Yii::t('app/book','Total in BYR')?></th>
                                                    <td><?=Yii::$app->formatter->asDecimal($arTotal['totalWithoutIgnore']);?></td>
                                                </tr>
                                            </table>
                                        <?php endif;?>
                                    </div>
                             </div>
                            </div>
                        </div>
</div>
