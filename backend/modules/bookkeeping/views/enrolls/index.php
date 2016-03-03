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
                                        'attribute' => 'unitname',
                                        'label' => Yii::t('app/book','Unit enroll name'),
                                        'value' => function($model){
                                            return is_object($obServ= $model->service) ? $obServ->enroll_unit : NULL;
                                        }
                                    ],
                                    [
                                        'attribute' => 'created_at',
                                        'format' => 'datetime',
                                        'label' => Yii::t('app/book','Processing date'),
                                        'filter' => \yii\jui\DatePicker::widget([
                                            'model'=>$searchModel,
                                            'attribute'=>'created_at',
                                            'language' => 'ru',
                                            'dateFormat' => 'dd.MM.yyyy',
                                            'options' =>['class' => 'form-control'],
                                            'clientOptions' => [
                                                'defaultDate' => date('d.m.Y',time())
                                            ],
                                        ]),
                                    ],
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

                <div class="col-md-4">
                <?=Html::tag('h3',Yii::t('app/book','Total'))?>
                <table class="table">
                    <?php foreach($arTotal as $key=>$value):?>
                        <tr>
                            <th><?=$key;?></th>
                            <td><?=Yii::$app->formatter->asDecimal($value);?></td>
                        </tr>
                    <?php endforeach;?>
                </table>
                </div>
                </div>
        </div>
    </div>
</div>
