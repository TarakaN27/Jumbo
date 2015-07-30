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
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],
                        'id',
                        'name',
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
                        'summ_from',
                        'summ_to',
                        [
                            'attribute' => 'currency_id',
                            'value' => function($model){
                                    return is_object($obCurr = $model->currency) ? $obCurr->code : 'N/A';
                                },
                            'filter' => \common\models\ExchangeRates::getRatesCodes()
                        ],
                        // 'corr_factor',
                        // 'commission',
                        // 'sale',
                        // 'tax',
                        // 'created_at',
                        // 'updated_at',

                        ['class' => 'yii\grid\ActionColumn'],
                    ],
                ]); ?>
            </div>
        </div>
    </div>
</div>
