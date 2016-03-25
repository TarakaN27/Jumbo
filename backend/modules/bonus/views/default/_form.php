<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\BonusScheme;
/* @var $this yii\web\View */
/* @var $model common\models\BonusScheme */
/* @var $form yii\widgets\ActiveForm */

$arServices = \common\models\Services::getAllServices();
$arLP = \common\models\LegalPerson::getLegalPersonMap();
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
        B_TYPE_COMPLEX = '.BonusScheme::TYPE_COMPLEX_TYPE.';
',\yii\web\View::POS_HEAD);
?>
<div id="preloader">
    <div class="loader mrg-auto"></div>
</div>
<div class="bonus-scheme-form hide">

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

    <?= $form->field($model, 'type')->dropDownList(
        $model::getTypeMap(),[
        'prompt' => Yii::t('app/bonus','Choose type')
    ]) ?>

    <?php
        $options = [];
        if(!in_array($model->type,[BonusScheme::TYPE_SIMPLE_BONUS,BonusScheme::TYPE_COMPLEX_TYPE]))
            $options['disabled'] = 'disabled';
        echo $form->field($model, 'num_month')->textInput($options) ?>

    <?= $form->field($model, 'grouping_type')->dropDownList($model::getGroupByMap(),[
        'prompt' => Yii::t('app/bonus','Choose grouping type')
    ]) ?>
    <div class="type1 <?=$model->type== BonusScheme::TYPE_UNITS ? '' : 'hide'?>">
        <?php foreach($arServices as $serv):?>
            <div class="col-md-4 col-sm-4 col-xs-12">
                <?=Html::tag('h4',$serv->name);?>
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
                            echo Html::textInput('costs['.$serv->id.']',$value,['class' => 'form-control']);
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
    <div class="type2 type3 <?= in_array($model->type,[BonusScheme::TYPE_COMPLEX_TYPE,BonusScheme::TYPE_SIMPLE_BONUS])? '' : 'hide'?>">
        <?php foreach($arServices as $serv):?>
            <div class="col-md-4 col-sm-4 col-xs-12">
                <?=Html::tag('h4',$serv->name);?>
                <hr>
                <div class="row">
                    <div class="col-md-6 col-sm-6 col-xs-12">
                    <?=Html::tag('h4',Yii::t('app/bonus','Deduct tax for legal person'))?>
                    <hr/>
                    <?php foreach($arLP as $key => $lp):?>
                        <div class="form-group">
                            <?php
                               $checked = FALSE;
                                if(isset($arBServices[$serv->id])) {
                                    $tmpLp = $arBServices[$serv->id]->legal_person;
                                    if(isset($tmpLp[$key]) && isset($tmpLp[$key]) == 1)
                                        $checked = TRUE;
                                }
                                echo Html::checkbox('legal['.$serv->id.']['.$key.']',$checked);
                            ?>
                            <?=Html::label($lp);?>
                        </div>
                    <?php endforeach;?>
                    </div>
                    <div class="col-md-6 col-sm-6 col-xs-12 ch_type3 <?= in_array($model->type,[BonusScheme::TYPE_COMPLEX_TYPE])? '' : 'hide'?>" >
                        <?=Html::tag('h4',Yii::t('app/bonus','Month percent'))?>
                        <hr/>
                        <div class="monthList" data-col="<?=$serv->id?>" data-num="<?=(int)$model->num_month?>">
                            <?php if(isset($arBServices[$serv->id]) && !empty($arBServices[$serv->id]->month_percent))
                                foreach($arBServices[$serv->id]->month_percent as $key => $item):
                            ?>
                            <?php
                                    $inputEl = Html::textInput('months['.$serv->id.']['.$key.']',$item,[
                                        'id' => 'mid_'.$serv->id.'_'.$key
                                    ]);
                                    $labelEl = Html::label($key);
                                    echo Html::tag('div',$labelEl.$inputEl,[
                                        'class' => 'form-group',
                                        'id' => 'div_mid_'.$serv->id.'_'.$key
                                    ]);
                                    ?>
                            <?php endforeach;?>
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-6 col-xs-12 simple_percent ch_type2 <?= in_array($model->type,[BonusScheme::TYPE_SIMPLE_BONUS])? '' : 'hide'?>">
                        <?=Html::tag('h4',Yii::t('app/bonus','Simple month percent'))?>
                        <hr/>
                        <?php
                            $valueSP = NULL;
                            if(isset($arBServices[$serv->id]))
                                $valueSP = $arBServices[$serv->id]->simple_percent;
                            echo Html::textInput('simple_percent['.$serv->id.']',$valueSP,['class' => 'form-control']);
                        ?>
                    </div>
                </div>
            </div>
        <?php endforeach;?>
    </div>

    <div class="form-group">
        <div class = "col-md-6 col-sm-6 col-xs-12">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app/bonus', 'Create') : Yii::t('app/bonus', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
