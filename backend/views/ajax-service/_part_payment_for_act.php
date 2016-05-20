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
        'id',
        'pay_summ:decimal',
        'currency.code',
        'pay_date:date',
        'service.name'
    ]
])?>