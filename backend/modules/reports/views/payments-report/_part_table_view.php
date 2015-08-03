<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 03.08.15
 */
?>
<div class="row">
<h3><?=Yii::t('app/reports','Total info')?></h3>
<table class="table table-bordered">
    <thead>
        <tr>
            <th><?=Yii::t('app/reports','iSumTotal')?></th>
            <th><?=Yii::t('app/reports','iProfitTotal')?></th>
            <th><?=Yii::t('app/reports','iTaxTotal')?></th>
            <th><?=Yii::t('app/reports','iProdTotal')?></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                <?=$model['iSumTotal'];?>
            </td>
            <td>
                <?=$model['iProfitTotal'];?>
            </td>
            <td>
                <?=$model['iTaxTotal'];?>
            </td>
            <td>
                <?=$model['iProdTotal'];?>
            </td>
        </tr>
    </tbody>
</table>
</div>
<div class="row">
<h3><?=Yii::t('app/reports','Detail info')?></h3>
<table class="table table-bordered">
    <thead>
        <tr>
            <th><?=Yii::t('app/reports','Contractor')?></th>
            <th><?=Yii::t('app/reports','Legal person')?></th>
            <th><?=Yii::t('app/reports','Service')?></th>
            <th><?=Yii::t('app/reports','Payment sum')?></th>
            <th><?=Yii::t('app/reports','Profit')?></th>
            <th><?=Yii::t('app/reports','Production')?></th>
            <th><?=Yii::t('app/reports','Tax')?></th>
        </tr>
    </thead>
    <tbody>
<?php foreach($model['data'] as $key => $data):?>
        <tr style="background-color:#f9f9f9">
            <td colspan="7">
                <?=$key;?>
            </td>
        </tr>
        <?php
    foreach($data as $dt):?>
        <tr>
            <td>
                    <?=is_object($cuser=$dt->cuser) ? $cuser->getInfo() : 'N/A';?>
            </td>
            <td>
                     <?=is_object($lp=$dt->legal) ? $lp->name : 'N/A';?>
            </td>
            <td>
                     <?=is_object($serv=$dt->service) ? $serv->name : 'N/A';?>
            </td>
            <td>
                    <?=$dt->pay_summ;?>
            </td>
            <td>
                    <?=is_object($calc=$dt->calculate) ? $calc->profit : 'N/A';?>
            </td>
            <td>
                    <?=is_object($calc=$dt->calculate) ? $calc->production : 'N/A';?>
            </td>
            <td>
                    <?=is_object($calc=$dt->calculate) ? $calc->tax : 'N/A';?>
            </td>

        </tr>
        <?php endforeach;
    ?>
<?php endforeach;?>
</tbody>
</table>
</div>

