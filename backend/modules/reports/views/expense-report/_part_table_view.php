<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 03.08.15
 */
use backend\modules\reports\forms\ExpenseReportForm;
use miloschuman\highcharts\Highcharts;

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
<?php

//var_dump($seriesData);die;
if(isset($model['graphArray']['data']))
echo Highcharts::widget([
    'options' => [
        'title' => ['text' => $model['graphArray']['type']],
        'xAxis' => [
            'categories' => array_keys($model['graphArray']['data'])
        ],
        'yAxis' => [
            'title' => ['text' => 'Затраты']
        ],
        'plotOptions'=> [
        'column'=> [
            'stacking'=> 'normal',
                'dataLabels'=> [
                'enabled'=> true,
                ]
            ]
        ],
        'series' => $model['graphArray']['data'],

    ]
]);

?>

<?php if(Yii::$app->user->can('adminRights')):?>
<div class="row">
<h3><?=Yii::t('app/reports','Total info')?></h3>
<table class="table table-bordered ">
    <thead>
        <tr>
            <th><?=Yii::t('app/reports','Expense total')?></th>
            <?php if(Yii::$app->user->can('superRights')):?>
            <th><?=Yii::t('app/reports','Reported total')?></th>
            <?php endif;?>
        </tr>
    </thead>
    <tbody>
        <tr>

            <td>
                <?=Yii::$app->formatter->asDecimal($model['iExpenseTotal']);?>
            </td>
            <?php if(Yii::$app->user->can('superRights')):?>
                <td>
                    <?=Yii::$app->formatter->asDecimal($model['iExpenseReportsTotal']);?>
                </td>
            <?php endif;?>
        </tr>
    </tbody>
</table>
</div>
<?php endif;?>
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
            <?php if($modelForm->groupType != ExpenseReportForm::GROUP_BY_CONTRACTOR):?>
            <th><?=Yii::t('app/reports','Contractor')?></th>
            <?php endif;?>
            <?php if(
                $modelForm->groupType == ExpenseReportForm::GROUP_BY_DATE ||
                $modelForm->groupType == ExpenseReportForm::GROUP_BY_PARENT_CATEGORY ||
                $modelForm->groupType == ExpenseReportForm::GROUP_BY_CONTRACTOR
            ):?>
                <th><?=Yii::t('app/reports','Responsibility')?></th>
            <?php endif;?>
            <?php if(
                $modelForm->groupType == ExpenseReportForm::GROUP_BY_CATEGORY ||
                $modelForm->groupType == ExpenseReportForm::GROUP_BY_LEGAL_PERSON ||
                $modelForm->groupType == ExpenseReportForm::GROUP_BY_CONTRACTOR
            ):?>
                <th><?=Yii::t('app/reports','Pay date')?></th>
            <?php endif;?>
            <th><?=Yii::t('app/reports','Legal person')?></th>
            <?php if($modelForm->groupType != ExpenseReportForm::GROUP_BY_LEGAL_PERSON):?>
            <th><?=Yii::t('app/reports','Service')?></th>
            <?php endif;?>
            <th><?=Yii::t('app/reports','Payment sum')?></th>
            <th><?=Yii::t('app/reports','Payment currency')?></th>
            <th><?=Yii::t('app/reports','Exchange currency')?></th>
            <?php if(Yii::$app->user->can('adminRights')):?>
            <th><?=Yii::t('app/reports','Sum BYR')?></th>
            <?php endif;?>
        </tr>
    </thead-->

    <?php foreach($model['data'] as $key => $data):?>
        <tbody class="item"
                 data-date = "<?= $key;?>"
                 data-summ = "<?=isset($model['totalGroupSum'][$key]) ? $model['totalGroupSum'][$key] : 0;?>"
               style="width: 100%;"
            >
        <tr style="background-color:#f9f9f9">
            <td colspan="13">
                <?php
                    switch($modelForm->groupType)
                    {
                        case \backend\modules\reports\forms\ExpenseReportForm::GROUP_BY_DATE:
                            echo Yii::$app->formatter->asDate($key);
                            break;
                        case ExpenseReportForm::GROUP_BY_LEGAL_PERSON:
                        case ExpenseReportForm::GROUP_BY_LEGAL_PERSON:
                        case ExpenseReportForm::GROUP_BY_CONTRACTOR:
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
            <th class="width-8-percent"><?=Yii::t('app/reports','Expense category')?></th>

            <th class="width-16-percent"><?=Yii::t('app/reports','Contractor')?></th>


            <th class="width-8-percent"><?=Yii::t('app/reports','Legal person')?></th>

            <th class="width-8-percent"><?=Yii::t('app/reports','Payment sum')?></th>
            <th class="width-4-percent"><?=Yii::t('app/reports','Payment currency')?></th>

            <?php if(Yii::$app->user->can('adminRights')):?>
            <th class="width-8-percent"><?=Yii::t('app/reports','Profit BYR')?></th>
            <?php endif;?>
        </tr>
        <?php
        foreach($data as $dt): ?>
        <tr>
            <td class="width-4-percent">    <?=\yii\helpers\Html::a(
                    $dt['id'],
                    ['/bookkeeping/default/view','id' => $dt['id']],
                    ['target' => '_blank']
                );?></td>
            <td class="width-8-percent">
                <?=($dt['cat_name']  ? $dt['cat_name'] : 'N/A');?>
            </td>

            <td class="width-8-percent">
                <?=($dt['full_corp_name']  ? $dt['full_corp_name'] : 'N/A');?>
            </td>

            <td class="width-8-percent">
                <?=($dt['legal_name'] ? $dt['legal_name'] : 'N/A');?>
            </td>

            <td class="width-8-percent">
                    <?=\yii\helpers\Html::a(Yii::$app->formatter->asDecimal($dt['pay_summ']),
                        ['/bookkeeping/default/view','id' => $dt['id']],
                        ['target' =>'_blank']
                        );?>
            </td>
            <td class="width-4-percent">    <?=($dt['code']?$dt['code'] : 'N/A');?></td>

            <?php if(Yii::$app->user->can('adminRights')):?>
            <td class="width-8-percent">
                    <?=($model['fullAmount'][$dt['id']] ? Yii::$app->formatter->asDecimal($model['fullAmount'][$dt['id']]) : 'N/A');?>
            </td>

            <?php endif;?>

        </tr>
        <?php endforeach;?>
        <tr class="wm-tr-total">
            <td colspan="6">
                <?=Yii::t('app/reports','Group total')?>
            </td>
            <td>
                <?=isset($model['totalGroupSum'][$key]) ? Yii::$app->formatter->asDecimal($model['totalGroupSum'][$key]) : '-';?>
            </td>


        </tr>
    </tbody>
<?php endforeach;?>
</table>
</div>

