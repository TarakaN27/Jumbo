<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 03.08.15
 */
use backend\modules\reports\forms\PaymentsReportForm;
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
            <?php if($modelForm->groupType != PaymentsReportForm::GROUP_BY_CONTRACTOR):?>
            <th><?=Yii::t('app/reports','Contractor')?></th>
            <?php endif;?>
            <?php if(
                $modelForm->groupType == PaymentsReportForm::GROUP_BY_DATE ||
                $modelForm->groupType == PaymentsReportForm::GROUP_BY_SERVICE ||
                $modelForm->groupType == PaymentsReportForm::GROUP_BY_CONTRACTOR
            ):?>
                <th><?=Yii::t('app/reports','Responsibility')?></th>
            <?php endif;?>
            <?php if(
                $modelForm->groupType == PaymentsReportForm::GROUP_BY_MANAGER ||
                $modelForm->groupType == PaymentsReportForm::GROUP_BY_SERVICE ||
                $modelForm->groupType == PaymentsReportForm::GROUP_BY_CONTRACTOR
            ):?>
                <th><?=Yii::t('app/reports','Pay date')?></th>
            <?php endif;?>
            <th><?=Yii::t('app/reports','Legal person')?></th>
            <?php if($modelForm->groupType != PaymentsReportForm::GROUP_BY_SERVICE):?>
            <th><?=Yii::t('app/reports','Service')?></th>
            <?php endif;?>
            <th><?=Yii::t('app/reports','Payment sum')?></th>
            <th><?=Yii::t('app/reports','Payment currency')?></th>
            <th><?=Yii::t('app/reports','Exchange currency')?></th>
            <th><?=Yii::t('app/reports','Profit BYR')?></th>
            <th><?=Yii::t('app/reports','Production BYR')?></th>
            <th><?=Yii::t('app/reports','Tax BYR')?></th>
            <th><?=Yii::t('app/reports','Payment calc condition')?></th>
            <th><?=Yii::t('app/reports','Condition currency')?></th>
        </tr>
    </thead>
    <tbody>
<?php foreach($model['data'] as $key => $data):?>
        <tr style="background-color:#f9f9f9">
            <td colspan="12">
                <?php
                    switch($modelForm->groupType)
                    {
                        case PaymentsReportForm::GROUP_BY_DATE:
                            echo Yii::$app->formatter->asDate($key);
                            break;
                        case PaymentsReportForm::GROUP_BY_MANAGER:
                        case PaymentsReportForm::GROUP_BY_SERVICE:
                        case PaymentsReportForm::GROUP_BY_CONTRACTOR:
                            echo $key;
                            break;
                        default:
                            echo $key;
                            break;
                    }
                ?>

            </td>
        </tr>
        <?php
        foreach($data as $dt): $cuser=$dt->cuser;?>
        <tr>
            <?php if($modelForm->groupType != PaymentsReportForm::GROUP_BY_CONTRACTOR):?>
            <td>
                    <?=is_object($cuser) ? $cuser->getInfo() : 'N/A';?>
            </td>
            <?php endif;?>

            <?php if(
                $modelForm->groupType == PaymentsReportForm::GROUP_BY_DATE ||
                $modelForm->groupType == PaymentsReportForm::GROUP_BY_SERVICE ||
                $modelForm->groupType == PaymentsReportForm::GROUP_BY_CONTRACTOR
            ):?>
            <td>
                    <?=is_object($cuser)&&is_object($obMan = $cuser->manager) ? $obMan->getFio() : 'N/A';?>
            </td>
            <?php endif;?>
            <?php if(
                $modelForm->groupType == PaymentsReportForm::GROUP_BY_MANAGER ||
                $modelForm->groupType == PaymentsReportForm::GROUP_BY_SERVICE ||
                $modelForm->groupType == PaymentsReportForm::GROUP_BY_CONTRACTOR
            ):?>
                <td>
                    <?=Yii::$app->formatter->asDate($dt->pay_date)?>
                </td>
            <?php endif;?>
            <td>
                     <?=is_object($lp=$dt->legal) ? $lp->name : 'N/A';?>
            </td>
            <?php if($modelForm->groupType != PaymentsReportForm::GROUP_BY_SERVICE):?>
            <td>
                     <?=is_object($serv=$dt->service) ? $serv->name : 'N/A';?>
            </td>
            <?php endif;?>
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
            <td>
                    <?=isset($model['condCurr'][$dt->id]) ? Yii::$app->formatter->asDecimal($model['condCurr'][$dt->id]) : 'N/A';?>
            </td>
        </tr>
        <?php endforeach;?>
        <tr class="wm-tr-total">
            <td colspan="4">
                <?=Yii::t('app/reports','Total')?>
            </td>
            <td>
                <?=isset($model['totalGroupSum'][$key]) ? Yii::$app->formatter->asDecimal($model['totalGroupSum'][$key]) : '-';?>
            </td>
            <td colspan="2">

            </td>
            <td>
                <?=isset($model['totalGroupProfit'][$key]) ? Yii::$app->formatter->asDecimal($model['totalGroupProfit'][$key]) : '-';?>
            </td>
            <td>
                <?=isset($model['totalGroupProd'][$key]) ? Yii::$app->formatter->asDecimal($model['totalGroupProd'][$key]) : '-';?>
            </td>
            <td>
                <?=isset($model['totalGroupTax'][$key]) ? Yii::$app->formatter->asDecimal($model['totalGroupTax'][$key]) : '-';?>
            </td>
            <td colspan="2">

            </td>
        </tr>

<?php endforeach;?>
</tbody>
</table>
</div>

