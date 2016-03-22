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
/*
$this->registerJs("
$('#bonusscheme-num_month').on('change',function(){
    var
        num = $(this).val();

    if(num == undefined || num < 0 || num =='')
    {
       $('.monthList').html('');
       $('.monthList').each(function( index ) {
            $(this).attr('data-num',0);
       });
       return false;
    }
    num = parseInt(num);
    var
        monthList = $('.monthList');    //get all month container

    monthList.each(function( index ) {
        var
            this1 = this,
            servID = $(this).attr('data-col'),
            currentNum = parseInt($(this).attr('data-num'));
        if(currentNum > num)        //if need remove element
            {
                for(var j = currentNum; j >= num+1;j--)
                {
                    $('#div_mid_'+servID +'_'+j).remove();
                }
            }else{                  //if need add new element
                for(var i = currentNum+1;i <= num;i++)
                {
                    var
                        input = $(document.createElement('input')), //input
                        label = $(document.createElement('label')), //label
                        div = $(document.createElement('div'));     //div container
                    label.html(i);
                    input.attr('name','months['+servID +']['+i+']');
                    input.attr('id','mid_'+servID +'_'+i);
                    div.addClass('form-group');
                    div.attr('id','div_mid_'+servID +'_'+i)
                    div.append(label);
                    div.append(input);
                    div.appendTo(this1);    //add to dom
                }
            }
        $(this).attr('data-num',num);       //set current number of month
    });
});
$('#preloader').remove();
$('.bonus-scheme-form').removeClass('hide');
",\yii\web\View::POS_READY);
*/
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

    <?= $form->field($model, 'num_month')->textInput() ?>

    <?= $form->field($model, 'inactivity')->textInput() ?>

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
                        <?=Html::textInput('costs['.$serv->id.']',null,['class' => 'form-control'])?>
                    </div>
                </div>
                <div class="form-group">
                        <?=Html::checkbox('multiple['.$serv->id.']',false)?>
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
                    <?php foreach($arLP as $key => $lp):?>
                        <div class="form-group">
                            <?=Html::checkbox('services['.$serv->id.']['.$key.']');?>
                            <?=Html::label($lp);?>
                        </div>
                    <?php endforeach;?>
                    </div>
                    <div class="col-md-6 col-sm-6 col-xs-12 monthList" data-col="<?=$serv->id?>" data-num="0">


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
