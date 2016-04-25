<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
$this->registerJsFile('@web/js/parts/partner_schemes.js',[
    'depends' => [
        'yii\web\JqueryAsset',
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapPluginAsset',
    ]
]);
/* @var $this yii\web\View */
/* @var $model common\models\PartnerSchemes */
/* @var $form yii\widgets\ActiveForm */
?>
<div id="preloader">
    <div class="loader mrg-auto"></div>
</div>
<div class="partner-schemes-form hide">

    <?php $form = ActiveForm::begin([
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

    <?= $form->field($model, 'start_period')->textInput() ?>

    <?= $form->field($model, 'regular_period')->textInput() ?>

    <?= $form->field($model,'currency_id')->dropDownList(\common\models\ExchangeRates::getRatesCodes())?>
    

    <div class="row">
        <?php $count = 1; foreach ($arServices as $servID => $servName):
            $countCol = 0;
            if(isset($arSchServ[$servID]) && isset($arSchServ[$servID]['ranges']))
            {
                $countCol = count($arSchServ[$servID]['ranges']);
                if($countCol > 0)
                    $countCol--;
            }
            ?>
            <div class="col-md-4 col-sm-4 col-xs-12 ">
                <?=Html::tag('h4',$servName,['class' => 'weight-bold-title']);?>
                <hr class="hr-green"/>
                <div class="col-md-12 col-sm-12 col-xs-12 well well-lg no-shadow">
                    <div class="col-md-12 col-sm-12 col-xs-12">
                        <?=Html::tag('h4',Yii::t('app/users','Partner service group')); ?>
                        <hr/>
                        <?php 
                            $value = NULL;
                            if(isset($arSchServ[$servID],$arSchServ[$servID]['group_id']))
                                $value = $arSchServ[$servID]['group_id'];
                            echo Html::dropDownList(
                                'group['.$servID.']',
                                $value,
                                \common\models\PartnerSchemesServicesGroup::getGroupMap(),
                                ['class' => 'form-control']
                            );
                        ?>
                    </div>
                    <div class="col-md-12 col-sm-12 col-xs-12">
                            <?=Html::tag('h4',
                                Yii::t('app/users','Ranges').' '.
                                Html::a('<span class="glyphicon glyphicon-plus" aria-hidden="true"></span>',NULL,[
                                    'data-col' => $countCol,
                                    'class' => 'addRange green',
                                    'data-serv' => $servID
                                ]).
                                '/'.
                                Html::a('<span class="glyphicon glyphicon-minus" aria-hidden="true"></span>',NULL,[
                                    'class' => 'removeRange',
                                    'data-serv' => $servID
                                ])
                            );?>
                        <hr/>
                        <div class="serviceBlock" data-col="0">
                            <table class="table table-bordered text-center">
                                <thead>
                                    <tr>
                                        <th>
                                            <?=Html::label(Yii::t('app/users','Min'));?>
                                        </th>
                                        <th>
                                            <?=Html::label(Yii::t('app/users','Max'));?>
                                        </th>
                                        <th>
                                            <?=Html::label(Yii::t('app/users','Percent'));?>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="servRange" data-serv="<?=$servID?>">
                                    <?php
                                        if(isset($arSchServ[$servID]) && isset($arSchServ[$servID]['ranges'])):
                                            foreach ($arSchServ[$servID]['ranges'] as $key => $item):
                                    ?>
                                    <tr class="" data-col="<?=$key;?>">
                                        <td>
                                            <?php
                                                $value = isset($item['left']) ? $item['left'] : NULL;
                                                echo Html::textInput('range['.$servID.']['.$key.'][left]',$value,['class' => 'form-control']);
                                            ?>
                                        </td>
                                        <td>
                                            <?php
                                                $value = isset($item['right']) ? $item['right'] : NULL;
                                                echo Html::textInput('range['.$servID.']['.$key.'][right]',$value,['class' => 'form-control']);
                                            ?>
                                        </td>
                                        <td>
                                            <?php
                                                $value = isset($item['percent']) ? $item['percent'] : NULL;
                                                echo Html::textInput('range['.$servID.']['.$key.'][percent]',$value,['class' => 'form-control']);
                                            ?>
                                        </td>
                                    </tr>
                                    <?php endforeach;else:?>
                                            <tr class="" data-col="0">
                                                <td>
                                                    <?=Html::textInput('range['.$servID.'][0][left]', NULL,['class' => 'form-control']);?>
                                                </td>
                                                <td>
                                                    <?=Html::textInput('range['.$servID.'][0][right]',NULL,['class' => 'form-control']);?>
                                                </td>
                                                <td>
                                                    <?=Html::textInput('range['.$servID.'][0][percent]',NULL,['class' => 'form-control']);?>
                                                </td>
                                            </tr>
                                    <?php endif;?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-md-12 col-sm-12 col-xs-12">
                        <?=Html::tag('h4',Yii::t('app/bonus','Deduct tax for legal person'))?>
                        <hr/>
                        <?php foreach($arLP as $key => $lp):?>
                            <div class="form-group">
                                <?php
                                $checked = FALSE;
                                if(isset($arSchServ[$servID])) {
                                    $tmpLp = $arSchServ[$servID]->legal;
                                    if(isset($tmpLp[$key]) && isset($tmpLp[$key]["deduct"]) && $tmpLp[$key]["deduct"] == '1')
                                        $checked = TRUE;
                                }
                                echo Html::checkbox('legal['.$servID.']['.$key.'][deduct]',$checked,[
                                    'data-id' => $servID.'_'.$key,
                                    'class' => 'legal-check-box'
                                ]);
                                ?>
                                <?=Html::label($lp);?>

                            </div>
                            <div class="form-group <?=$checked ? '' : 'hide';?>" id="<?=$servID.'_'.$key;?>">
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
                                            if(isset($arSchServ[$servID])) {
                                                $tmpLp = $arSchServ[$servID]->legal;
                                                if(isset($tmpLp[$key]) && isset($tmpLp[$key]['res']) && $tmpLp[$key]['res'] == '1')
                                                    $checked = TRUE;
                                            }
                                            echo Html::checkbox('legal['.$servID.']['.$key.'][res]',$checked);
                                            ?>
                                        </td>
                                        <td>
                                            <?php
                                            $value = NULL;
                                            if(isset($arSchServ[$servID])) {
                                                $tmpLp = $arSchServ[$servID]->legal;
                                                if(isset($tmpLp[$key]) && isset($tmpLp[$key]['res_tax']))
                                                    $value = $tmpLp[$key]['res_tax'];
                                            }
                                            echo Html::textInput('legal['.$servID.']['.$key.'][res_tax]',$value)?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            <?=Html::label(Yii::t('app/bonus','Not resident'));?>
                                        </th>
                                        <td>
                                            <?php
                                            $checked = FALSE;
                                            if(isset($arSchServ[$servID])) {
                                                $tmpLp = $arSchServ[$servID]->legal;
                                                if(isset($tmpLp[$key]) && isset($tmpLp[$key]['not_res']) && $tmpLp[$key]['not_res'] == 1)
                                                    $checked = TRUE;
                                            }
                                            echo Html::checkbox('legal['.$servID.']['.$key.'][not_res]',$checked);
                                            ?>
                                        </td>
                                        <td>
                                            <?php
                                            $value = NULL;
                                            if(isset($arSchServ[$servID])) {
                                                $tmpLp = $arSchServ[$servID]->legal;
                                                if(isset($tmpLp[$key]) && isset($tmpLp[$key]['not_res_tax']))
                                                    $value = $tmpLp[$key]['not_res_tax'];
                                            }
                                            echo Html::textInput('legal['.$servID.']['.$key.'][not_res_tax]',$value);
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
            <?php if($count%3 == 0):?>
                <div class="col-md-12 col-sm-12 col-xs-12 "></div>
            <?php endif; $count++;?>
        <?php endforeach;?>
    </div>
    <div class="form-group">
        <div class = "col-md-12 col-sm-12 col-xs-12 ">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app/users', 'Create') : Yii::t('app/users', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>
