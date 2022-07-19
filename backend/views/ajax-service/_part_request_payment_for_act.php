<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 20.5.16
 * Time: 17.08
 */
?>
<p>Пустые платежи</p>

<?=common\components\customComponents\gridView\CustomGridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        [
            'class' => 'yii\grid\CheckboxColumn',
            'checkboxOptions' => function ($model) {
                return [
                    'value' => $model->id,
                    'class' => 'cbPayment ',
                    'data-sum' => (float)$model->pay_summ,
                    'data-curr' => $model->currency_id,
                    'data-date' => Yii::$app->formatter->asDate($model->pay_date),
                    'data-serv_id' => $model->service_id
                ];
            }
        ],
        [
            'attribute' => 'pay_summ',
            'format' => 'html',
            'value' => function($model) use ($arRedisPaymentRequest){
                $postfix = in_array($model->id,$arRedisPaymentRequest) ? ' <span class="label label-primary">New</span>' : '';
                $paySumm = Yii::$app->formatter->asDecimal($model->pay_summ,Yii::$app->params['decimalRound']);
                return $paySumm.$postfix;
            }
        ],
        [
            'attribute' => 'currency_id',
            'value' => function($model){
                return is_object($obCar = $model->currency) ? $obCar->code : 'N/A';
            }
        ],
        [
            'attribute' => 'cntr_id',
            'format' => 'html',
            'value' => function($model){
                return is_object($obCUser = $model->cuser) ? $obCUser->getInfo() : NULL;
            }
        ],
        [
            'attribute' => 'legal_id',
            'format' => 'html',
            'value' => function($model){
                return is_object($obLegal = $model->legal) ? $obLegal->name : NULL;
            }
        ],
        [
            'attribute' => 'pay_date',
            'format' => 'date'
        ],
        [
            'attribute' => 'service_id',
            'value' => function($model){
                return \common\models\Services::getServicesMap()[$model->service_id];
            },
        ]
    ]
])?>
