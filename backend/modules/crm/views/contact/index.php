<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\web\JsExpression;
/* @var $this yii\web\View */
/* @var $searchModel common\models\search\CrmCmpContactsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app/crm', 'Crm Cmp Contacts');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class = "row">
    <div class = "col-md-12 col-sm-12 col-xs-12">
        <div class = "x_panel">
            <div class = "x_title">
                <h2><?= Html::encode($this->title) ?></h2>
                <section class="pull-right">
                    <?= Html::a(Yii::t('app/crm', 'Create Crm Cmp Contacts'), ['create'], ['class' => 'btn btn-success']) ?>
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
                            'attribute' => 'fio',
                            'format' => 'html',
                            'value' => function($model) use ($arContactRedis){

                                $addStr = in_array($model->id,$arContactRedis) ? '<span class="label label-info">New</span>' : '';
                                return Html::a($model->fio,['view','id' => $model->id],['class' => 'link-upd']).' '.$addStr;
                            }
                        ],
                        'post',
                        [
                            'attribute' => 'type',
                            'value' => function($model){
                                return $model->getTypeStr();
                            },
                            'filter' => \common\models\CrmCmpContacts::getTypeArr()
                        ],
                        [
                            'attribute' => 'cmp_id',
                            'value' => function($model){
                                return is_object($obCmp = $model->cmp) ? $obCmp->getInfoWithSite() : $model->cmp_id;
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
                        'phone',
                        'email',
                        [
                            'attribute' => 'assigned_at',
                            'value' => function($model){
                                return is_object($obAss = $model->assignedAt) ? $obAss->getFio() : $model->assigned_at;
                            },
                            'filter' => \kartik\select2\Select2::widget([
                                'model' => $searchModel,
                                'attribute' => 'assigned_at',
                                'initValueText' => $buserDesc, // set the initial display text
                                'options' => [
                                    'placeholder' => Yii::t('app/crm','Search for a users ...')
                                ],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                    'minimumInputLength' => 3,
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
                        ]
                    ],
                ]); ?>
            </div>
        </div>
    </div>
</div>
