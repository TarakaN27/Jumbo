<?php

use yii\helpers\Html;
use yii\grid\GridView;
use \common\models\PaymentRequest;
/* @var $this yii\web\View */
/* @var $searchModel common\models\search\PaymentRequestSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app/book', 'Payment Requests');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="payment-request-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'pay_summ',
            [
                'attribute' => 'currency_id',
                'value' => function($model){
                        return is_object($obCar = $model->currency) ? $obCar->code : 'N/A';
                    },
                'filter' => \common\models\ExchangeRates::getRatesCodes()
            ],
            [
                'attribute' => 'cntr_id',
                'value' => function($model){
                        return is_object($obCUser = $model->cuser) ? $obCUser->getInfo() : NULL;
                    }
            ],
            [
                'attribute' => 'is_unknown',
                'value' => function($model){
                        return $model->getYesNoStr($model->is_unknown);
                    },
                'filter' => \common\models\PaymentRequest::getYesNo()
            ],
            'user_name',
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
            [
                'attribute' => 'owner_id',
                'value' => function($model){
                        return is_object($obOn = $model->owner) ? $obOn->getFio() : NULL;
                    }
            ],

            // 'legal_id',
            // 'description:ntext',
            // 'dialog_id',
             [
                 'attribute' => 'status',
                 'value' => function($model){
                        return $model->getStatusStr();
                     },
                 'filter' => \common\models\PaymentRequest::getStatusArr()
             ],
            // 'created_at',
            // 'updated_at',

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view}{process}',
                'buttons' => [
                    'process' =>function ($url, $model, $key) {
                        if(empty($model->manager_id) && $model->is_unknown == PaymentRequest::YES)
                        {
                            $options = [
                                'title' => Yii::t('yii', 'Make mine payment'),
                                'aria-label' => Yii::t('yii', 'Make mine payment'),
                            ];
                            return Html::a(
                                '<span class="glyphicon glyphicon-pushpin"></span>',
                                \yii\helpers\Url::to(['pin-payment-to-manager','pID' => $model->id]),
                                $options);
                        }
                        elseif(!empty($model->manager_id) && in_array($model->status,[PaymentRequest::STATUS_NEW]) && !empty($model->cntr_id)){
                            $options = [
                                'title' => Yii::t('yii', 'Add payments'),
                                'aria-label' => Yii::t('yii', 'Add payments'),
                            ];
                            if(Yii::$app->user->can('only_manager'))
                                return Html::a(
                                    '<span class="glyphicon glyphicon-credit-card"></span>',
                                    \yii\helpers\Url::to(['add-payment','pID' => $model->id]),
                                    $options);
                        }

                        return '';
                    }
                ]

            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{delete}',
                'buttons' => [
                    'delete' => function ($url, $model, $key) {
                            $options = [
                                'title' => Yii::t('yii', 'Delete'),
                                'aria-label' => Yii::t('yii', 'Delete'),
                                'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
                                'data-method' => 'post',
                                'data-pjax' => '0',
                            ];
                            if($model->owner_id == Yii::$app->user->id)
                                return Html::a('<span class="glyphicon glyphicon-trash"></span>', $url, $options);
                        }
                ]
            ]

        ]
    ]); ?>

</div>
