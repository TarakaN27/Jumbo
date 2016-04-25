<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 21.4.16
 * Time: 13.30
 */
?>
<?php if(!empty($data['incoming'])):?>
    <?=\yii\helpers\Html::tag('h4',Yii::t('app/users','Partner incoming amount'))?>
    <hr>
    <table class="table table-striped table-bordered">
        <?foreach ($data['incoming'] as $leadID => $leadStat):?>
        <tr class="leadHead">

            <td colspan="2">
                <?=isset($data['arLeads'],$data['arLeads'][$leadID]) ? $data['arLeads'][$leadID] : $leadID;?>
            </td>
            <td>
                <?=Yii::$app->formatter->asDecimal($leadStat['stat']['payAmount'])?> / <?=Yii::$app->formatter->asDecimal($leadStat['stat']['amount'])?>
            </td>
        </tr>
            <?php foreach ($leadStat['services'] as $servID => $servStat):?>
                <tr class="servHead">
                    <td colspan="2">
                        <?=isset($data['arService'],$data['arService'][$servID]) ? $data['arService'][$servID] : $servID;?>
                    </td>
                    <td>
                        <?=Yii::$app->formatter->asDecimal($servStat['detail']['payAmount'])?> / <?=Yii::$app->formatter->asDecimal($servStat['detail']['amount'])?>
                    </td>
                </tr>
                <tr>
                    <td colspan="3">
                        <?=\yii\grid\GridView::widget([
                            'dataProvider' => new \yii\data\ArrayDataProvider([
                                'allModels' => $servStat['stat']
                            ]),
                            'layout' => "{items}\n{pager}",
                            'columns' => [
                                'payment_id',
                                'payment.pay_summ:decimal',
                                'payment.currency.code',
                                'payment.pay_date:date',
                                'amount:decimal',
                                'percent',
                                'created_at:datetime'
                            ]
                        ])?>
                    </td>
                </tr>
            <?php endforeach;?>

        <?php endforeach;?>
    </table>
<?php endif;?>
<?php if(!empty($data['withdrawal'])):?>
<?=\yii\helpers\Html::tag('h4',Yii::t('app/users','Partner expense amount'))?>
<hr>
    <?=\yii\grid\GridView::widget([
        'dataProvider' => new \yii\data\ArrayDataProvider([
            'allModels' => $data['withdrawal'],
        ]),
        'columns' => [
            'amount:decimal',
            'percent',
            'created_at:datetime'
        ]
    ])?>
<?php endif;?>
<?php if(!empty($data['fullStat'])):?>
<?=\yii\helpers\Html::tag('h4',Yii::t('app/users','Partner full stat'))?>
<hr>
    <?=\common\components\customComponents\gridView\CustomGridView::widget([
        'dataProvider' => new \yii\data\ArrayDataProvider([
            'allModels' => $data['fullStat'],
        ]),
        'addTrClass' => function($model){
            return $model->type == \common\models\PartnerPurseHistory::TYPE_INCOMING ? 'incomingPurse' : 'expensePurse';
        },
        'tableOptions' => [
            'class' => 'table table-bordered'
        ],
        'columns' => [
            [
                'attribute' => 'type',
                'value' => 'typeStr'
            ],
            'payment_id',
            'payment.pay_summ:decimal',
            'payment.currency.code',
            'payment.pay_date:date',
            'expense_id',
            'amount:decimal',
            'percent',
            'created_at:datetime'
        ]

    ])?>
<?php endif;?>



