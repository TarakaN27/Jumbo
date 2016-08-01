<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\search\PaymentConditionSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app/book', 'Payment Conditions');
$this->params['breadcrumbs'][] = $this->title;
?>


<div class = "row">
    <div class = "col-md-12 col-sm-12 col-xs-12">
        <div class = "x_panel">
            <div class = "x_title">
                <h2><?php echo $this->title?></h2>
                <section class="pull-right">
                    <?= Html::a(Yii::t('app/book', 'Create Payment Condition'), ['create'], ['class' => 'btn btn-success']) ?>
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
                            'attribute' => 'name',
                            'format' => 'html',
                            'value' => function($model)
                                {
                                    return Html::a($model->name,['update','id'=>$model->id],['class'=>'link-upd']);
                                }
                        ],
                        [
                            'attribute' => 'service_id',
                            'value' => function($model){
                                return is_object($obServ = $model->service) ? $obServ->name : 'N/A';
                                },
                            'filter' => \common\models\Services::getServicesMap()
                        ],
                        [
                            'attribute' => 'l_person_id',
                            'value' => function($model){
                                    return is_object($obLP = $model->lPerson) ? $obLP->name : 'N/A';
                                },
                            'filter' => \common\models\LegalPerson::getLegalPersonMap()
                        ],
                        [
                            'attribute' => 'summ_from',
                            'format'=>['decimal',4]
                        ],
                        [
                            'attribute' => 'summ_to',
                            'format'=>['decimal',4]
                        ],
                        [
                            'attribute' => 'currency_id',
                            'value' => function($model){
                                    return is_object($obCurr = $model->currency) ? $obCurr->code : 'N/A';
                                },
                            'filter' => \common\models\ExchangeRates::getRatesCodes()
                        ],
                        [
                            'attribute' => 'cond_currency',
                            'value' => function($model){
                                return is_object($obCC = $model->condCurrency) ? $obCC->code : 'N/A';
                            },
                            'filter' => \common\models\ExchangeRates::getRatesCodes()
                        ],
                        [
                            'attribute' => 'type',
                            'value' => function($model){
                                return $model->getTypeStr();
                            },
                            'filter' => \common\models\PaymentCondition::getTypeArr()
                        ],
                        [
                            'attribute' => 'corr_factor',
                            'format'=>['decimal',10]
                        ],
                        [
                            'attribute' => 'commission',
                            'format'=>['decimal',10]
                        ],
                        [
                            'attribute' => 'sale',
                            'format'=>['decimal',10]
                        ],
                        [
                            'attribute' => 'tax',
                            'format'=>['decimal',10]
                        ],
                        [
                            'attribute' => 'not_use_sale',
                            'format' => 'boolean',
                            'filter' => \common\models\PaymentCondition::getYesNo()
                        ],
                        [
                            'attribute' => 'not_use_corr_factor',
                            'format' => 'boolean',
                            'filter' => \common\models\PaymentCondition::getYesNo()
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
