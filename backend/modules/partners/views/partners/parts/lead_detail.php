<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 21.4.16
 * Time: 13.30
 */
?>
<?php if(!empty($data['incoming'])):?>
    <table class="table table-striped table-bordered">
        <?foreach ($data['incoming'] as $leadID => $leadStat):?>
        <tr class="leadHead">

            <td colspan="2">
                <?=isset($data['arLeads'],$data['arLeads'][$leadID]) ? $data['arLeads'][$leadID] : $leadID;?>
            </td>
            <td>
                <?=$leadStat['stat']['payAmount']?>/<?=$leadStat['stat']['amount']?>
            </td>
        </tr>
            <?php foreach ($leadStat['services'] as $servID => $servStat):?>
                <tr class="servHead">
                    <td colspan="2">
                        <?=$servID;?>
                    </td>
                    <td>
                        <?=$servStat['detail']['payAmount']?>/<?=$servStat['detail']['amount']?>
                    </td>
                </tr>
                <tr>
                    <td colspan="3">
                        <?=\yii\grid\GridView::widget([
                            'dataProvider' => new \yii\data\ArrayDataProvider([
                                'allModels' => $servStat['stat']
                            ]),
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


