<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 20.5.16
 * Time: 17.08
 */
use common\models\Payments;
?>
<?=common\components\customComponents\gridView\CustomGridView::widget([
    'dataProvider' => new \yii\data\ArrayDataProvider([
        'allModels' => $arPayments,
        'pagination' => [
            'defaultPageSize' => 1000,
            'pageSizeLimit' => [1,1000]
        ],
    ]),
    'addTrClass' => function(Payments $model){
        return $model->hide_act_payment ? 'act-hide-payment' : '';
    },
    'columns' => [
        [
            'class' => 'yii\grid\CheckboxColumn',
            'checkboxOptions' => function (\common\models\Payments $model, $key, $index, $column) {
                return [
                    'value' => $model->id,
                    'class' => 'cbPayment ',
                    'data-sum' => (float)$model->pay_summ-(float)$model->actAmount,
                    'data-curr' => $model->currency_id,
                    'data-date' => Yii::$app->formatter->asDate($model->pay_date),
                    'data-serv_id' => $model->service_id,
                    'data-hide' => $model->hide_act_payment
                ];
            }
        ],
        'id',
        'pay_summ:decimal',
        'actAmount:decimal',
        'currency.code',
        'payment_order',
        'pay_date:date',
        'service.name',
        [
            'attribute' => 'enrollStatus',
            'value' => 'enrollStatusStr'
        ]
    ]
])?>