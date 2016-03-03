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
            <th><?=Yii::t('app/reports','controllSumm')?></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                <?=Yii::$app->formatter->asDecimal($model['iSumTotal']);?>
            </td>
            <td>
                <?=Yii::$app->formatter->asDecimal($model['iProfitTotal']);?>
            </td>
            <td>
                <?=Yii::$app->formatter->asDecimal($model['iTaxTotal']);?>
            </td>
            <td>
                <?=Yii::$app->formatter->asDecimal($model['iProdTotal']);?>
            </td>
            <td>
                <?=Yii::$app->formatter->asDecimal($model['summControll']);?>
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
            <th><?=Yii::t('app/reports','Payment currency')?></th>
            <th><?=Yii::t('app/reports','Exchange currency')?></th>
            <th><?=Yii::t('app/reports','Profit BYR')?></th>
            <th><?=Yii::t('app/reports','Production BYR')?></th>
            <th><?=Yii::t('app/reports','Tax BYR')?></th>
            <th><?=Yii::t('app/reports','Payment calc condition')?></th>
        </tr>
    </thead>
    <tbody>
<?php foreach($model['data'] as $key => $data):?>
        <tr style="background-color:#f9f9f9">
            <td colspan="10">
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
                    <?=\yii\helpers\Html::a(Yii::$app->formatter->asDecimal($dt->pay_summ),
                        ['/bookkeeping/default/view','id' => $dt->id],
                        ['target' =>'_blank']
                        );?>
            </td>
            <td>    <?=is_object($curr = $dt->currency) ? $curr->code : 'N/A';?></td>
            <td>
                    <?=isset($model['currency'][$dt->id]) ? Yii::$app->formatter->asDecimal($model['currency'][$dt->id]) : 'N/A'?>
            </td>
            <td>
                    <?=is_object($calc=$dt->calculate) ? Yii::$app->formatter->asDecimal($calc->profit) : 'N/A';?>
            </td>
            <td>
                    <?=is_object($calc=$dt->calculate) ? Yii::$app->formatter->asDecimal($calc->production) : 'N/A';?>
            </td>
            <td>
                    <?=is_object($calc=$dt->calculate) ? Yii::$app->formatter->asDecimal($calc->tax) : 'N/A';?>
            </td>
            <td>
                <?=is_object($calc=$dt->calculate) ? (is_object($cond = $calc->payCond) ? $cond->name : 'N/A') : 'N/A';?>
            </td>
        </tr>
        <?php endforeach;
    ?>
<?php endforeach;?>
</tbody>
</table>
</div>

