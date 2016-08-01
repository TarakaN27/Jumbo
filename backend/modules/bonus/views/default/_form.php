<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\BonusScheme;
use common\components\helpers\CustomViewHelper;
/* @var $this yii\web\View */
/* @var $model common\models\BonusScheme */
/* @var $form yii\widgets\ActiveForm */

$arServices = \common\models\Services::getAllServices();
$arLP = \common\models\LegalPerson::getLegalPersonMap();
CustomViewHelper::registerJsFileWithDependency('@web/js/accounting/accounting.min.js',$this,[],'accounting');
$this->registerJsFile('@web/js/parts/bonus_form.js',[
    'depends' => [
        'yii\web\JqueryAsset',
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapPluginAsset',
    ]
]);
$this->registerJs('
    var
        B_TYPE_UNIT = '.BonusScheme::TYPE_UNITS.',
        B_TYPE_SIMPLE = '.BonusScheme::TYPE_SIMPLE_BONUS.',
        B_TYPE_COMPLEX = '.BonusScheme::TYPE_COMPLEX_TYPE.',
        B_TYPE_COMPLEX_PARTNER = '.BonusScheme::TYPE_COMPLEX_PARTNER.',
        B_TYPE_PAYMENT_RECORDS = '.BonusScheme::TYPE_PAYMENT_RECORDS.';
',\yii\web\View::POS_HEAD);
?>
<div id="preloader">
    <div class="loader mrg-auto"></div>
</div>
<div class="bonus-scheme-form hide">

    <?php $form = ActiveForm::begin([
        'id' => 'bonusFormId',
        'options' => [
            'class' => 'form-horizontal form-label-left',
            //'enctype' => 'multipart/form-data'
        ],
        'fieldConfig' => [
            'template' => '<div class="form-group">{label}<div class="col-md-6 col-sm-6 col-xs-12">{input}</div><ul class="parsley-errors-list" >{error}</ul></div>',
            'labelOptions' => ['class' => 'control-label col-md-3 col-sm-3 col-xs-12'],
        ],
    ]); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'type')->dropDownList(
            $model::getTypeMap(),[
            'prompt' => Yii::t('app/bonus','Choose type')
        ]) ?>

    <?php
        $options = [];
        if(!in_array($model->type,[BonusScheme::TYPE_SIMPLE_BONUS,BonusScheme::TYPE_COMPLEX_TYPE,BonusScheme::TYPE_COMPLEX_PARTNER]))
            $options['disabled'] = 'disabled';
        echo $form->field($model, 'num_month')->textInput($options) ?>
    <?php if(!in_array($model->type,[BonusScheme::TYPE_PAYMENT_RECORDS])):?>
    <?= $form->field($model, 'grouping_type')->dropDownList($model::getGroupByMap(),[
        'prompt' => Yii::t('app/bonus','Choose grouping type'),]) ?>
    <?php endif;?>

    <div class="type2 type3  <?=!in_array($model->type,[BonusScheme::TYPE_UNITS,BonusScheme::TYPE_PAYMENT_RECORDS]) ? '' : 'hide'?>">
        <?= $form->field($model, 'payment_base')->dropDownList(BonusScheme::getPaymentBaseArr())?>
    </div>

    <div class="type5 <?=$model->type== BonusScheme::TYPE_PAYMENT_RECORDS ? '' : 'hide'?>">
        <?= $form->field($model,'currency_id')->dropDownList(\common\models\ExchangeRates::getRatesCodes())?>
    </div>
    <div class="type1 <?=$model->type== BonusScheme::TYPE_UNITS ? '' : 'hide'?>">
        <?php foreach($arServices as $serv):?>
            <div class="col-md-4 col-sm-4 col-xs-12">
                <?=Html::tag('h4',$serv->name,['class' => 'weight-bold']);?>
                <hr>
                <div class="form-group">
                    <?=Html::label(Yii::t('app/bonus','Стоимость'),null,[
                        'class' => 'pdd-top-8 col-md-4 col-sm-4 col-xs-12'
                    ]);?>
                    <div class="col-md-6 col-sm-6 col-xs-12">
                        <?php
                            $value = NULL;
                            if(isset($arBServices[$serv->id]))
                                $value = $arBServices[$serv->id]->cost;
                            echo Html::textInput('costs['.$serv->id.']',$value,['class' => 'form-control costs']);
                        ?>
                    </div>
                </div>
                <div class="form-group">
                        <?php
                            $checked = FALSE;
                            if(isset($arBServices[$serv->id]) && $arBServices[$serv->id]->unit_multiple)
                                $checked = TRUE;
                            echo Html::checkbox('multiple['.$serv->id.']',$checked);
                        ?>
                        <?=Html::label(Yii::t('app/bonus','Multiple'),null,[
                            'class' => 'pdd-top-8'
                        ]);?>
                </div>
            </div>
        <?php endforeach;?>
    </div>
    <div class="type2 type3 type4 <?= in_array($model->type,[BonusScheme::TYPE_COMPLEX_TYPE,BonusScheme::TYPE_SIMPLE_BONUS,BonusScheme::TYPE_COMPLEX_PARTNER])? '' : 'hide'?>">
        <?php foreach($arServices as $serv):?>
            <div class="col-md-4 col-sm-4 col-xs-12">
                <?=Html::tag('h4',$serv->name,['class' => 'weight-bold-title']);?>
                <hr class="hr-green"/>
                <div class="row">
                    <div class="col-md-12 col-sm-12 col-xs-12 ch_type3 <?= in_array($model->type,[BonusScheme::TYPE_COMPLEX_TYPE,BonusScheme::TYPE_COMPLEX_PARTNER])? '' : 'hide'?>" >
                        <?=Html::tag('h4',Yii::t('app/bonus','Month percent'))?>
                        <hr/>
                        <div class="monthList" data-col="<?=$serv->id?>" data-num="<?=(int)$model->num_month?>">
                            <?php
                                $countMonth = 0;

                            if(isset($arBServices[$serv->id]) && !empty($arBServices[$serv->id]->month_percent))
                            {
                                $countMonth = count($arBServices[$serv->id]->month_percent);
                                foreach($arBServices[$serv->id]->month_percent as $key => $item) {

                                    $inputEl = Html::textInput('months[' . $serv->id . '][' . $key . ']', $item, [
                                        'id' => 'mid_' . $serv->id . '_' . $key
                                    ]);
                                    $labelEl = Html::label($key);
                                    echo Html::tag('div', $labelEl . $inputEl, [
                                        'class' => 'form-group',
                                        'id' => 'div_mid_' . $serv->id . '_' . $key
                                    ]);
                                }
                            }

                            for ($i = $countMonth+1;$i<=(int)$model->num_month;$i++)
                            {
                                $inputEl = Html::textInput('months[' . $serv->id . '][' . $i . ']', NULL, [
                                    'id' => 'mid_' . $serv->id . '_' . $i
                                ]);
                                $labelEl = Html::label($i);
                                echo Html::tag('div', $labelEl . $inputEl, [
                                    'class' => 'form-group',
                                    'id' => 'div_mid_' . $serv->id . '_' . $i
                                ]);
                            }
                            ?>


                        </div>
                    </div>
                    <div class="col-md-12 col-sm-12 col-xs-12 simple_percent ch_type2 <?= in_array($model->type,[BonusScheme::TYPE_SIMPLE_BONUS])? '' : 'hide'?>">
                        <?=Html::tag('h4',Yii::t('app/bonus','Simple month percent'))?>
                        <hr/>
                        <?php
                        $valueSP = NULL;
                        if(isset($arBServices[$serv->id]))
                            $valueSP = $arBServices[$serv->id]->simple_percent;
                        echo Html::textInput('simple_percent['.$serv->id.']',$valueSP,['class' => 'form-control']);
                        ?>
                    </div>
                    <div class="col-md-12 col-sm-12 col-xs-12">
                    <?=Html::tag('h4',Yii::t('app/bonus','Deduct tax for legal person'))?>
                    <hr/>
                    <?php foreach($arLP as $key => $lp):?>
                        <div class="form-group">
                            <?php
                               $checked = FALSE;
                                if(isset($arBServices[$serv->id])) {
                                    $tmpLp = $arBServices[$serv->id]->legal_person;
                                    if(isset($tmpLp[$key]) && isset($tmpLp[$key]["deduct"]) && $tmpLp[$key]["deduct"] == '1')
                                        $checked = TRUE;
                                }
                                echo Html::checkbox('legal['.$serv->id.']['.$key.'][deduct]',$checked,[
                                    'data-id' => $serv->id.'_'.$key,
                                    'class' => 'legal-check-box'
                                ]);
                            ?>
                            <?=Html::label($lp);?>

                        </div>
                        <div class="form-group <?=$checked ? '' : 'hide';?>" id="<?=$serv->id.'_'.$key;?>">
                            <table class="table table-bordered text-center">
                                <tr>
                                    <th>

                                    </th>
                                    <th class="text-center">
                                        <?=Html::label(Yii::t('app/bonus','deduct tax'));?>
                                    </th>
                                    <th class="text-center">
                                        <?=Html::label(Yii::t('app/bonus','Custom tax'));?>
                                    </th>
                                </tr>
                                <tr>
                                    <th >
                                        <?=Html::label(Yii::t('app/bonus','Resident'));?>
                                    </th>
                                    <td>
                                        <?php
                                            $checked = FALSE;
                                            if(isset($arBServices[$serv->id])) {
                                                $tmpLp = $arBServices[$serv->id]->legal_person;
                                                if(isset($tmpLp[$key]) && isset($tmpLp[$key]['res']) && $tmpLp[$key]['res'] == '1')
                                                    $checked = TRUE;
                                            }
                                            echo Html::checkbox('legal['.$serv->id.']['.$key.'][res]',$checked);
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                            $value = NULL;
                                            if(isset($arBServices[$serv->id])) {
                                                $tmpLp = $arBServices[$serv->id]->legal_person;
                                                if(isset($tmpLp[$key]) && isset($tmpLp[$key]['res_tax']))
                                                    $value = $tmpLp[$key]['res_tax'];
                                            }
                                            echo Html::textInput('legal['.$serv->id.']['.$key.'][res_tax]',$value)?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <?=Html::label(Yii::t('app/bonus','Not resident'));?>
                                    </th>
                                    <td>
                                        <?php
                                            $checked = FALSE;
                                            if(isset($arBServices[$serv->id])) {
                                                $tmpLp = $arBServices[$serv->id]->legal_person;
                                                if(isset($tmpLp[$key]) && isset($tmpLp[$key]['not_res']) && $tmpLp[$key]['not_res'] == 1)
                                                    $checked = TRUE;
                                            }
                                            echo Html::checkbox('legal['.$serv->id.']['.$key.'][not_res]',$checked);
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                            $value = NULL;
                                            if(isset($arBServices[$serv->id])) {
                                                $tmpLp = $arBServices[$serv->id]->legal_person;
                                                if(isset($tmpLp[$key]) && isset($tmpLp[$key]['not_res_tax']))
                                                    $value = $tmpLp[$key]['not_res_tax'];
                                            }
                                            echo Html::textInput('legal['.$serv->id.']['.$key.'][not_res_tax]',$value);
                                        ?>
                                    </td>
                                </tr>
                            </table>
                        <hr/>
                        </div>
                    <?php endforeach;?>
                    </div>

                </div>
            </div>
        <?php endforeach;?>
    </div>

    <div class="pdd-left-10 pdd-right-10 type5 <?=$model->type== BonusScheme::TYPE_PAYMENT_RECORDS ? '' : 'hide'?>">
        <div class="form-group">
            <label class="control-label col-md-3 col-sm-3 col-xs-12"><?=Yii::t('app/bonus','Rates record bonus')?></label>
            <div class="col-md-6 col-sm-6 col-xs-12 well text-center">
                <button type="button" id="addRecordId" data-curr-num="0" class="btn btn-info btn-xs"><i class="fa fa-plus-square"></i></button>
                <button type="button" id="removeRecordId" class="btn btn-danger btn-xs"><i class="fa fa-minus-square"></i></button>
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-4 col-sm-4 col-xs-12">
                            <?=Html::label(Yii::t('app/bonus','from'))?>
                        </div>
                        <div class="col-md-4 col-sm-4 col-xs-12">
                            <?=Html::label(Yii::t('app/bonus','to'))?>
                        </div>
                        <div class="col-md-4 col-sm-4 col-xs-12">
                            <?=Html::label(Yii::t('app/bonus','Rate'))?>
                        </div>
                    </div>
                </div>

                <section id="recordContainer">
                    <?php foreach ($arRates as $key => $item):?>
                        <div class="form-group" data-col="$key">
                            <div class="row">
                                <div class="col-md-4 col-sm-4 col-xs-12">
                                    <?=Html::textInput("records[".$key."][from]",$item['from'],['class' => 'form-control'])?>
                                </div>
                                <div class="col-md-4 col-sm-4 col-xs-12">
                                    <?=Html::textInput("records[".$key."][to]",$item['to'],['class' => 'form-control'])?>
                                </div>
                                <div class="col-md-4 col-sm-4 col-xs-12">
                                    <?=Html::textInput("records[".$key."][rate]",$item['rate'],['class' => 'form-control'])?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach;?>
                </section>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-md-3 col-sm-3 col-xs-12"><?=Yii::t('app/bonus','Deduct tax for legal person')?></label>
            <div class="col-md-6 col-sm-6 col-xs-12 well">
                <?php $checked=false; $value = NULL; foreach ($arLP as $iLPId => $lpName):?>
                    <div class="row">
                    <div class="form-group col-md-3 col-sm-3 col-xs-12">
                        <label>
                            <?php
                                $checked = isset($arRecordLpDeduct[$iLPId],$arRecordLpDeduct[$iLPId]['deduct']) && $arRecordLpDeduct[$iLPId]['deduct'] == 1 ? TRUE : FALSE;
                                echo Html::checkbox('record-lp['.$iLPId.'][deduct]',$checked,['data-id' => $iLPId,'class' => 'deductRecordCheck']);?>
                            <?=$lpName;?>
                        </label>
                    </div>
                    <div class="form-group col-md-9 col-sm-9 col-xs-12 <?=$checked ? '' : 'hide'?>" id="rlp_group_id_<?=$iLPId;?>">
                        <table class="table table-bordered text-center">
                            <tr>
                                <th>

                                </th>
                                <th class="text-center">
                                    <?=Html::label(Yii::t('app/bonus','deduct tax'));?>
                                </th>
                                <th class="text-center">
                                    <?=Html::label(Yii::t('app/bonus','Custom tax'));?>
                                </th>
                            </tr>
                            <tr>
                                <th >
                                    <?=Html::label(Yii::t('app/bonus','Resident'));?>
                                </th>
                                <td>
                                    <?php
                                    $checked = isset($arRecordLpDeduct[$iLPId],$arRecordLpDeduct[$iLPId]['res']) && $arRecordLpDeduct[$iLPId]['res'] == 1 ? TRUE : FALSE;
                                    echo Html::checkbox('record-lp['.$iLPId.'][res]',$checked);
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    $value = isset($arRecordLpDeduct[$iLPId],$arRecordLpDeduct[$iLPId]['res_tax']) ? $arRecordLpDeduct[$iLPId]['res_tax'] : FALSE;
                                    echo Html::textInput('record-lp['.$iLPId.'][res_tax]',$value)?>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <?=Html::label(Yii::t('app/bonus','Not resident'));?>
                                </th>
                                <td>
                                    <?php
                                    $checked = isset($arRecordLpDeduct[$iLPId],$arRecordLpDeduct[$iLPId]['not_res']) && $arRecordLpDeduct[$iLPId]['not_res'] == 1 ? TRUE : FALSE;
                                    echo Html::checkbox('record-lp['.$iLPId.'][not_res]',$checked);
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    $value = isset($arRecordLpDeduct[$iLPId],$arRecordLpDeduct[$iLPId]['not_res_tax']) ? $arRecordLpDeduct[$iLPId]['not_res_tax'] : FALSE;
                                    echo Html::textInput('record-lp['.$iLPId.'][not_res_tax]',$value);
                                    ?>
                                </td>
                            </tr>
                        </table>
                        <hr/>
                    </div>
                    </div>
                <?php endforeach;?>
            </div>
        </div>
    </div>

    <div class="form-group">
        <div class = "col-md-12 col-sm-12 col-xs-12">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app/bonus', 'Create') : Yii::t('app/bonus', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
