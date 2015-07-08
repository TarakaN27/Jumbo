<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\search\PaymentsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app/book', 'Payments');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class = "page-title">
    <div class = "title_left">
         <h3><?php $this->title?></h3>
    </div>

    <div class = "title_right">

    </div>
</div>
<div class = "clearfix"></div>
<div class = "row">

<div class = "col-md-12 col-sm-12 col-xs-12">
                            <div class = "x_panel">
                                <div class = "x_title">
                                    <h2><?php echo $this->title?></h2>
                                    <section class="pull-right">
                                    <?= Html::a(Yii::t('app/book', 'Create Payments'), ['create'], ['class' => 'btn btn-success']) ?>
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
            [
                'attribute' => 'cuser_id',
                'value' => function($model){
                        return is_object($cuser = $model->cuser) ? $cuser->username : 'N/A';
                    },
                'filter' => \common\models\CUser::getContractorMap()
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

            'pay_summ',
            [
                'attribute' => 'currency_id',
                'value' => function($model){
                        return is_object($cur = $model->currency) ? $cur->code : 'N/A';
                    },
                'filter' => \common\models\ExchangeRates::getRatesCodes()
            ],
            [
                'attribute' => 'pay_date',
                'value' => function($model){
                        return $model->getFormatedPayDate();
                    },
                'filter' => \yii\jui\DatePicker::widget([

                        'model'=>$searchModel,
                        'attribute'=>'pay_date',
                        'language' => 'ru',
                        'dateFormat' => 'dd-MM-yyyy',
                        'options' =>['class' => 'form-control'],
                        'clientOptions' => [
                            'defaultDate' => date('d-m-Y',time())
                        ],
                    ]),
                'format' => 'raw',
            ],
            // 'service_id',
            // 'legal_id',
            // 'description:ntext',
            // 'created_at',
            // 'updated_at',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

                             </div>
                            </div>
                        </div>
</div>
