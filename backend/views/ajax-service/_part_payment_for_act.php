<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 20.5.16
 * Time: 17.08
 */
?>
<?=common\components\customComponents\gridView\CustomGridView::widget([
    'dataProvider' => new \yii\data\ArrayDataProvider([
        'allModels' => $arPayments
    ]),
    'columns' => [
        [
            'class' => 'yii\grid\CheckboxColumn',
            'checkboxOptions' => function (\common\models\Payments $model, $key, $index, $column) {
                return [
                    'value' => $model->id,
                    'class' => 'cbPayment',
                    'data-sum' => $model->pay_summ,
                    'data-curr' => $model->currency_id,
                    'data-date' => $model->pay_date,
                    'data-serv_id' => $model->service_id
                ];
            }
        ],
        'id',
        'pay_summ:decimal',
        'currency.code',
        'pay_date:date',
        'service.name',
    ]
])?>