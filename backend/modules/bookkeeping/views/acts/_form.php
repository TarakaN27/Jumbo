<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use Yii;
/* @var $this yii\web\View */
/* @var $model common\models\Acts */
/* @var $form yii\widgets\ActiveForm */
$checkBoxTpl = '<div class="form-group">{label}<div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">{input}</div><ul class="parsley-errors-list" >{error}</ul></div>';
$this->registerJs("
function checkGen()
{
    var
        gen = $('#acts-genfile'),
        file = $('#acts-file_name');

    if(gen.is(':checked'))
        {
            file.attr('disabled','disabled');
        }else
        {
            file.removeAttr('disabled');
        }
}
function findContractNumber()
{
    var
        iCID = $('#acts-cuser_id').val(),
        iLP = $('#acts-lp_id').val(),
        iServ = $('#acts-service_id').val();

    if(iCID && iServ && iLP)
        {
            $.ajax({
                type: \"POST\",
                cache: false,
                url: '".\yii\helpers\Url::to(['find-contact-number'])."',
                dataType: \"json\",
                data: {iCID:iCID,iServ:iServ,iLP:iLP},
                success: function(msg){
                    if(!msg)
                      {
                            addWarningNotify('".Yii::t('app/book','Find contract number request')."','".Yii::t('app/book','Find contract number request FAIL')."');

                      }else{

                            $('#acts-contract_date').val(msg.date);
                            $('#acts-contract_num').val(msg.num);
                            addSuccessNotify('".Yii::t('app/book','Find contract number request')."','".Yii::t('app/book','Contract detail successfully find')."');
                      }
                },
                error: function(msg){
                    addErrorNotify('".Yii::t('app/book','Find contract number request')."','".Yii::t('app/book','Server error')."');
                    return false;
                }
            });
        }

    return false;
}

function findActTemplate()
{
    var
        iLP = $('#acts-lp_id').val();

    if(iLP)
            {
                $.ajax({
                    type: \"POST\",
                    cache: false,
                    url: '".\yii\helpers\Url::to(['find-act-template'])."',
                    dataType: \"json\",
                    data: {iLP:iLP},
                    success: function(msg){
                        if(!msg)
                          {
                                addWarningNotify('".Yii::t('app/book','Find act template request')."','".Yii::t('app/book','Find act template request FAIL')."');

                          }else{
                                $('#acts-template_id').val(msg.tpl);
                                addSuccessNotify('".Yii::t('app/book','Find act template request')."','".Yii::t('app/book','Act template successfully find')."');
                          }
                    },
                    error: function(msg){
                        addErrorNotify('".Yii::t('app/book','Find act template request')."','".Yii::t('app/book','Server error')."');
                        return false;
                    }
                });
            }

        return false;

}
",\yii\web\View::POS_END);
$this->registerJs("
$('.form-group').on('change','#acts-genfile',function(){
    checkGen();
});
$('#acts-cuser_id,#acts-lp_id,#acts-service_id').on('change',function(){
    findContractNumber();
});
$('#acts-lp_id').on('change',function(){
    findActTemplate();
});

",\yii\web\View::POS_READY)
?>

<div class="acts-form">

    <?php $form = ActiveForm::begin([
        'options' => [
            'class' => 'form-horizontal form-label-left',
            'enctype' => 'multipart/form-data'
        ],
        'fieldConfig' => [
            'template' => '<div class="form-group">{label}<div class="col-md-6 col-sm-6 col-xs-12">{input}</div><ul class="parsley-errors-list" >{error}</ul></div>',
            'labelOptions' => ['class' => 'control-label col-md-3 col-sm-3 col-xs-12'],
        ],
    ]); ?>

    <?= $form->field($model, 'cuser_id')->widget(Select2::classname(), [
        'data' => \common\models\CUser::getContractorMap(),
        'options' => ['placeholder' => Yii::t('app/book','BOOK_choose_cuser')],
        'pluginOptions' => [
            'allowClear' => true
        ],
    ]); ?>

    <?= $form->field($model, 'service_id')->dropDownList(\common\models\Services::getServicesMap(),[
        'prompt' => Yii::t('app/book','BOOK_choose_service')
    ]) ?>

    <?= $form->field($model,'lp_id')->dropDownList(\common\models\LegalPerson::getLegalPersonMap(),[
        'prompt' => Yii::t('app/book','Choose legal person')
    ])?>

    <?= $form->field($model, 'amount')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model,'contract_num')->textInput()?>

    <?= $form->field($model,'contract_date')->widget(\kartik\date\DatePicker::className(),[
        'type' => \kartik\date\DatePicker::TYPE_COMPONENT_PREPEND,
        'pluginOptions' => [
            'autoclose'=>true,
            'format' => 'yyyy-m-dd'
        ]
    ])?>

    <?= $form->field($model, 'template_id')->dropDownList(\common\models\ActsTemplate::getActsTplMap()) ?>

    <?= $form->field($model,'act_num')->textInput()?>

    <?= $form->field($model, 'act_date')->widget(\kartik\date\DatePicker::className(),[
        'type' => \kartik\date\DatePicker::TYPE_COMPONENT_PREPEND,
        'pluginOptions' => [
            'autoclose'=>true,
            'format' => 'yyyy-m-dd'
        ]
    ]) ?>

    <?= $form->field($model,'genFile',['template' => $checkBoxTpl])->checkbox();?>
    <?= $form->field($model,'file_name')->fileInput();?>
    <?= \common\components\entityFields\widget\EntityFieldsFormWidget::widget([
        'form' => $form,
        'model' => $model
    ]);?>
    <div class="form-group">
        <div class = "col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app/book', 'Create') : Yii::t('app/book', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
