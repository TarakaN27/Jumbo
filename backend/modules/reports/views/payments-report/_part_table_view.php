<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 03.08.15
 */
use backend\modules\reports\forms\PaymentsReportForm;
$this->registerJsFile('@web/js/isotope/isotope.js',[
    'depends' => [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset'
    ],
]);
$this->registerJs("
$('.container-result').isotope({
  getSortData : {
    summ : function ( elem ) {
      return parseFloat($(elem).attr('data-summ'));
    },
    date : function ( elem ) {
      return $(elem).attr('data-date');
    },
    profit: function(elem){
        return parseFloat($(elem).attr('data-profit'));
    },
    tax: function(elem){
        return parseFloat($(elem).attr('data-tax'));
    },
    prod: function(elem){
        return parseFloat($(elem).attr('data-prod'));
    }
  },
  itemSelector : '.item',
});

$('#sort-type').on('change',function(){
    var
        container = $('.container-result');
        value = $(this).val();
        value = value.split('-');

    container.isotope({
      sortBy: value[0],
      sortAscending: value[1] == 'false' ? false:true
    });
});


",\yii\web\View::POS_READY);

$arSort = [];
$isAdmin = Yii::$app->user->can('adminRights');

if(Yii::$app->user->can('adminRights'))
{
    $arSort = [
        'date-false' =>  Yii::t('app/reports','Default'),
        'summ-true' =>  Yii::t('app/reports','Summ total A-Z'),
        'summ-false' =>  Yii::t('app/reports','Summ total Z-A'),
        'profit-true' =>  Yii::t('app/reports','Profit total A-Z'),
        'profit-false' =>  Yii::t('app/reports','Profit total Z-A'),
        'prod-true' =>  Yii::t('app/reports','Prod total A-Z'),
        'prod-false' =>  Yii::t('app/reports','Prod total Z-A'),
        'tax-true' =>  Yii::t('app/reports','Tax total A-Z'),
        'tax-false' =>  Yii::t('app/reports','Tax total Z-A'),
    ];
}else{
    $arSort = [
        'date-false' =>  Yii::t('app/reports','Default'),
        'summ-true' =>  Yii::t('app/reports','Summ total A-Z'),
        'summ-false' =>  Yii::t('app/reports','Summ total Z-A'),
    ];
}

?>
<div class="row">
<h3><?=Yii::t('app/reports','Total info')?></h3>
<table class="table table-bordered ">
    <thead>
        <tr>
            <th><?=Yii::t('app/reports','iSumTotal')?></th>
            <th><?=Yii::t('app/reports','Acted sum')?></th>
            <th><?=Yii::t('app/reports','Other Period Sum')?></th>
            <?php if($modelForm->showWithoutSale):?>
                <th><?=Yii::t('app/reports','saleAmount')?></th>
                <th><?=Yii::t('app/reports','paymentAmountWithoutSale')?></th>
            <?php endif;?>
            <?php if(Yii::$app->user->can('adminRights')):?>
            <th><?=Yii::t('app/reports','iProfitTotal')?></th>
            <th><?=Yii::t('app/reports','iTaxTotal')?></th>
            <th><?=Yii::t('app/reports','iProdTotal')?></th>
            <th><?=Yii::t('app/reports','controllSumm')?></th>
            <?php endif;?>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                <?=Yii::$app->formatter->asDecimal($model['iSumTotal']);?>
            </td>
            <td>
                <?=Yii::$app->formatter->asDecimal($model['iActedSumTotal']);?>
            </td>
            <td>
                <?=Yii::$app->formatter->asDecimal($model['totalSumOtherPeriod']);?>
            </td>
            <?php if($modelForm->showWithoutSale):?>
                <td><?=is_null($model['saleAmount']) ? NULL : Yii::$app->formatter->asDecimal($model['saleAmount']);?></td>
                <td><?=is_null($model['paymentWithoutSale']) ? NULL : Yii::$app->formatter->asDecimal($model['paymentWithoutSale']);?></td>
            <?php endif;?>
            <?php if(Yii::$app->user->can('adminRights')):?>
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
            <?php endif;?>
        </tr>
    </tbody>
</table>
</div>
<div class="row">
    <div class="col-md-6">
        <h3><?=Yii::t('app/reports','Detail info')?></h3>
    </div>
    <div class="col-md-6">
        <?=\yii\helpers\Html::dropDownList('Sorting',null,$arSort,
        [
            'id' => 'sort-type',
            'class' => 'pull-right form-control width-30-percent'
        ])?>
    </div>
<table class="table table-bordered container-result width-100-percent">
    <!--thead>
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
            <th><?=Yii::t('app/reports','Acted sum')?></th>
            <th><?=Yii::t('app/reports','Payment currency')?></th>
            <th><?=Yii::t('app/reports','Exchange currency')?></th>
            <?php if(Yii::$app->user->can('adminRights')):?>
            <th><?=Yii::t('app/reports','Profit BYR')?></th>
            <th><?=Yii::t('app/reports','Production BYR')?></th>
            <th><?=Yii::t('app/reports','Tax BYR')?></th>
            <?php endif;?>
            <th><?=Yii::t('app/reports','Payment calc condition')?></th>
            <th><?=Yii::t('app/reports','Condition currency')?></th>
        </tr>
    </thead-->

    <?php foreach($model['data'] as $key => $data):?>
        <tbody class="item"
                 data-date = "<?= $key;?>"
                 data-summ = "<?=isset($model['totalGroupSum'][$key]) ? $model['totalGroupSum'][$key] : 0;?>"
                 data-acted-summ = "<?=isset($model['totalGroupActedSum'][$key]) ? $model['totalGroupActedSum'][$key] : 0;?>"
                 data-profit = "<?=isset($model['totalGroupProfit'][$key]) ? $model['totalGroupProfit'][$key] : 0;?>"
                 data-tax = "<?=isset($model['totalGroupTax'][$key]) ? $model['totalGroupTax'][$key] : 0;?>"
                 data-prod = "<?=isset($model['totalGroupProd'][$key]) ? $model['totalGroupProd'][$key] : 0;?>"
               style="width: 100%;"
            >
        <tr style="background-color:#f9f9f9">
            <td colspan="14">
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
        <tr>
            <th class="width-4-percent"><?=Yii::t('app/reports','Payments ID')?></th>
            <?php if($modelForm->groupType != PaymentsReportForm::GROUP_BY_CONTRACTOR):?>
                <th class="width-16-percent"><?=Yii::t('app/reports','Contractor')?></th>
            <?php endif;?>
            <?php if(
                $modelForm->groupType == PaymentsReportForm::GROUP_BY_DATE ||
                $modelForm->groupType == PaymentsReportForm::GROUP_BY_SERVICE ||
                $modelForm->groupType == PaymentsReportForm::GROUP_BY_CONTRACTOR
            ):?>
                <th class="width-8-percent"><?=Yii::t('app/reports','Payment owner')?></th>
            <?php endif;?>
            <th class="width-8-percent"><?=Yii::t('app/reports','Sale owner')?></th>
            <?php if(
                $modelForm->groupType == PaymentsReportForm::GROUP_BY_MANAGER ||
                $modelForm->groupType == PaymentsReportForm::GROUP_BY_SERVICE ||
                $modelForm->groupType == PaymentsReportForm::GROUP_BY_CONTRACTOR
            ):?>
                <th class="width-8-percent"><?=Yii::t('app/reports','Pay date')?></th>
            <?php endif;?>
            <th class="width-8-percent"><?=Yii::t('app/reports','Legal person')?></th>
            <?php if($modelForm->groupType != PaymentsReportForm::GROUP_BY_SERVICE):?>
                <th class="<?php if($isAdmin):?>width-8-percent<?php else:?>width-12-percent <?php endif;?>"><?=Yii::t('app/reports','Service')?></th>
            <?php endif;?>
            <th class="width-8-percent"><?=Yii::t('app/reports','Payment sum')?></th>
            <th class="width-8-percent"><?=Yii::t('app/reports','Acted sum')?></th>
            <th class="width-4-percent"><?=Yii::t('app/reports','Payment currency')?></th>
            <th class="<?php if($isAdmin):?>width-4-percent<?php else:?>width-8-percent <?php endif;?>"><?=Yii::t('app/reports','Exchange currency')?></th>
            <?php if(Yii::$app->user->can('adminRights')):?>
            <th class="width-8-percent"><?=Yii::t('app/reports','Profit BYR')?></th>
            <th class="width-8-percent"><?=Yii::t('app/reports','Production BYR')?></th>
            <th class="width-8-percent"><?=Yii::t('app/reports','Tax BYR')?></th>
            <?php endif;?>
            <th class="<?php if($isAdmin):?>width-12-percent<?php else:?>width-16-percent <?php endif;?>"><?=Yii::t('app/reports','Payment calc condition')?></th>
            <th class="width-4-percent"><?=Yii::t('app/reports','Condition currency')?></th>
        </tr>
        <?php
        foreach($data as $dt): ?>
        <tr>
            <td class="width-4-percent">    <?=\yii\helpers\Html::a(
                    $dt['id'],
                    ['/bookkeeping/default/view','id' => $dt['id']],
                    ['target' => '_blank']
                );?></td>
            <?php if($modelForm->groupType != PaymentsReportForm::GROUP_BY_CONTRACTOR):?>
            <td class="width-16-percent">
                    <?=($dt['full_corp_name'] ? $dt['full_corp_name'] : 'N/A');?>
            </td>
            <?php endif;?>

            <?php if(
                $modelForm->groupType == PaymentsReportForm::GROUP_BY_DATE ||
                $modelForm->groupType == PaymentsReportForm::GROUP_BY_SERVICE ||
                $modelForm->groupType == PaymentsReportForm::GROUP_BY_CONTRACTOR
            ):?>
            <td class="width-8-percent">
                    <?=($dt['manager_name']  ? $dt['manager_name'] : 'N/A');?>
            </td>

            <?php endif;?>
            <td class="width-8-percent">
                <?=($dt['sale_manager_name']  ? $dt['sale_manager_name'] : 'N/A');?>
            </td>
            <?php if(
                $modelForm->groupType == PaymentsReportForm::GROUP_BY_MANAGER ||
                $modelForm->groupType == PaymentsReportForm::GROUP_BY_SERVICE ||
                $modelForm->groupType == PaymentsReportForm::GROUP_BY_CONTRACTOR
            ):?>
                <td class="width-8-percent">
                    <?=Yii::$app->formatter->asDate($dt['pay_date'])?>
                </td>
            <?php endif;?>
            <td class="width-8-percent">
                     <?=($dt['legal_name'] ? $dt['legal_name'] : 'N/A');?>
            </td>
            <?php if($modelForm->groupType != PaymentsReportForm::GROUP_BY_SERVICE):?>
            <td class="<?php if($isAdmin):?>width-8-percent<?php else:?>width-12-percent <?php endif;?>">
                     <?=($dt['service_name'] ? $dt['service_name'] : 'N/A');?>
            </td>
            <?php endif;?>
            <td class="width-4-percent">
                    <?=\yii\helpers\Html::a(Yii::$app->formatter->asDecimal($dt['pay_summ']),
                        ['/bookkeeping/default/view','id' => $dt['id']],
                        ['target' =>'_blank']
                        );?>
            </td>
            <td class="width-4-percent">
                <?=\yii\helpers\Html::a(Yii::$app->formatter->asDecimal($dt['acted_sum']),
                    ['/bookkeeping/default/view','id' => $dt['id']],
                    ['target' =>'_blank']
                );?>
            </td>
            <td class="width-4-percent">    <?=($dt['code']?$dt['code'] : 'N/A');?></td>
            <td class="<?php if($isAdmin):?>width-4-percent<?php else:?>width-8-percent <?php endif;?>">
                    <?=isset($model['currency'][$dt['id']]) ? str_replace('.',',',$model['currency'][$dt['id']]) : 'N/A'?>
            </td>
            <?php if(Yii::$app->user->can('adminRights')):?>
            <td class="width-8-percent">
                    <?=($dt['profit'] ? Yii::$app->formatter->asDecimal($dt['profit']) : 'N/A');?>
            </td>
            <td class="width-8-percent">
                    <?=($dt['production']) ? Yii::$app->formatter->asDecimal($dt['production']) : 'N/A';?>
            </td>
            <td class="width-8-percent">
                    <?=($dt['tax']) ? Yii::$app->formatter->asDecimal($dt['tax']) : 'N/A';?>
            </td>
            <?php endif;?>
            <td class="<?php if($isAdmin):?>width-12-percent<?php else:?>width-16-percent <?php endif;?>">
                    <?=($dt['pay_cond_name']? $dt['pay_cond_name'] : 'N/A');?>
            </td>
            <td class="width-4-percent">
                    <?=isset($model['condCurr'][$dt['id']]) ? str_replace('.',',',$model['condCurr'][$dt['id']]) : 'N/A';?>
            </td>
        </tr>
        <?php endforeach;?>
        <tr class="wm-tr-total">
            <td colspan="6">
                <?=Yii::t('app/reports','Group total')?>
            </td>
            <td>
                <?=isset($model['totalGroupSum'][$key]) ? Yii::$app->formatter->asDecimal($model['totalGroupSum'][$key]) : '-';?>
            </td>
            <td>
                <?=isset($model['totalGroupActedSum'][$key]) ? Yii::$app->formatter->asDecimal($model['totalGroupActedSum'][$key]) : '-';?>
            </td>
            <td colspan="2">

            </td>
            <?php if(Yii::$app->user->can('adminRights')):?>
            <td>
                <?=isset($model['totalGroupProfit'][$key]) ? Yii::$app->formatter->asDecimal($model['totalGroupProfit'][$key]) : '-';?>
            </td>
            <td>
                <?=isset($model['totalGroupProd'][$key]) ? Yii::$app->formatter->asDecimal($model['totalGroupProd'][$key]) : '-';?>
            </td>
            <td>
                <?=isset($model['totalGroupTax'][$key]) ? Yii::$app->formatter->asDecimal($model['totalGroupTax'][$key]) : '-';?>
            </td>
            <?php endif;?>
            <td colspan="2">

            </td>
        </tr>
    </tbody>
<?php endforeach;?>
</table>
</div>

