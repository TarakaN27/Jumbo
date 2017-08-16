<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;

/* @var $this yii\web\View */
/* @var $model common\models\CUser */
/* @var $form yii\widgets\ActiveForm */
$fieldTempl = '<div>{label}{input}</div><ul class="parsley-errors-list" >{error}</ul>';
$fieldTemplSecond = '<div class="form-group"><div class="col-md-6 col-sm-6 col-xs-12">{label}{input}<ul class="parsley-errors-list" >{error}</ul></div></div>';
$this->registerJs('
function modeResident()
{
    var
        rrb = $(".RRB"),
        norrb = $(".NORRB"),
        resMode = $("#cuser-is_resident").val();
    if(resMode == '.\common\models\CUser::RESIDENT_NO.')
        {
            rrb.hide();
            norrb.show();
            $("#cuserrequisites-isresident").val("false");
        }else{
            rrb.show();
            norrb.hide();
            $("#cuserrequisites-isresident").val("true");
        }
}

',\yii\web\View::POS_END);

$this->registerJs('
modeResident();
$("#cuser-is_resident").on("change",modeResident);
',\yii\web\View::POS_READY);


$this->registerJs("
function blockRequisitesTypes(){
    var
        jPersonInfo = $('.jPersonInfo'),
        passportBlock = $('.passportBlock'),
        regPersonInfo = $('.regPersonInfo'),
        bankRequisites = $('.bankRequisites'),
        jAddress = $('.jAddress'),
        pAddress = $('.pAddress'),
        contactInfo = $('.contactInfo'),
        blockHide = $('.hideBlockClass'),
        currType = $('#cuserrequisites-type_id input:checked').val();

        blockHide.hide();
        switch(currType) {
        case '".\common\models\CUserRequisites::TYPE_F_PERSON."':
            passportBlock.show();
            pAddress.show();
            contactInfo.show();
            break;
        case '".\common\models\CUserRequisites::TYPE_J_PERSON."':
            jPersonInfo.show();
            regPersonInfo.show();
            bankRequisites.show();
            jAddress.show();
            pAddress.show();
            contactInfo.show();
            break;
        case '".\common\models\CUserRequisites::TYPE_I_PERSON."':
            passportBlock.show();
            regPersonInfo.show();
            bankRequisites.show();
            pAddress.show();
            contactInfo.show();
            break;
        default:
            console.log('default value');
            break;
}

}
",\yii\web\View::POS_END);
$this->registerJs("
blockRequisitesTypes();
$('#cuserrequisites-type_id input').on('click',blockRequisitesTypes);
",\yii\web\View::POS_READY);

?>

<div class = "x_content">
    <br />
    <?php $form = ActiveForm::begin([
        'options' => [
            'class' => 'form-horizontal form-label-left'
        ],
        'fieldConfig' => [
            'template' => '<div class="form-group"><div class="col-md-6 col-sm-6 col-xs-12">{label}{input}</div><ul class="parsley-errors-list" >{error}</ul></div>',
            'labelOptions' => ['class' => 'control-label'],
        ],
    ]); ?>
        <?php echo Html::activeHiddenInput($modelR,'isResident'); ?>
        <?= $form->field($modelR, 'type_id',[
            'template'=>$fieldTemplSecond,
            'labelOptions'=>['class' => 'control-label']])
            ->radioList(\common\models\CUserRequisites::getTypeArr(),['class'=>'radioType']) ?>
        <div class = "form-group">
            <div class = "col-md-4 col-sm-4 col-xs-12">
        <?= $form->field($model,'is_resident', ['template' => $fieldTempl,'labelOptions'=>['class' => 'control-label']])->dropDownList(\common\models\CUser::getResidentArr());?>
                </div><div class = "col-md-4 col-sm-4 col-xs-12">
        <?= $form->field($model,'r_country', ['template' => $fieldTempl,'labelOptions'=>['class' => 'control-label']])->textInput(['maxlength' => true])?>
       </div></div>
<section class="jPersonInfo hideBlockClass">
            <div class="ln_solid"></div>
            <h4 class = "h4_ml_10">Сведение о юридическом лице</h4>
    <?= $form->field($modelR, 'corp_name',['template'=>$fieldTemplSecond,'labelOptions'=>['class' => 'control-label']])->textInput(['maxlength' => TRUE]) ?>
        </section>

        <div class="ln_solid"></div>
        <h4 class = "h4_ml_10">Лицо, уполномоченное на заключение договора</h4>
        <div class = "form-group">
            <div class = "col-md-4 col-sm-4 col-xs-12">
                <?= $form->field($modelR, 'j_lname', ['template' => $fieldTempl,'labelOptions'=>['class' => 'control-label']])->textInput(['maxlength' => TRUE]) ?>
            </div>
            <div class = "col-md-4 col-sm-4 col-xs-12">
                <?= $form->field($modelR, 'j_fname', ['template' => $fieldTempl,'labelOptions'=>['class' => 'control-label']])->textInput(['maxlength' => TRUE]) ?>
            </div>
            <div class = "col-md-4 col-sm-4 col-xs-12">
                <?= $form->field($modelR, 'j_mname', ['template' => $fieldTempl,'labelOptions'=>['class' => 'control-label']])->textInput(['maxlength' => TRUE]) ?>
            </div>
            <div class = "col-md-4 col-sm-4 col-xs-12 jPersonInfo hideBlockClass">
                <?= $form->field($modelR, 'j_post', ['template' => $fieldTempl,'labelOptions'=>['class' => 'control-label']])->textInput(['maxlength' => TRUE]) ?>
            </div>
            <div class = "col-md-4 col-sm-4 col-xs-12 jPersonInfo hideBlockClass">
                <?= $form->field($modelR, 'j_doc', ['template' => $fieldTempl,'labelOptions'=>['class' => 'control-label']])->textInput(['maxlength' => TRUE]) ?>
            </div>
        </div>
        <div class="ln_solid"></div>
        <div class = "form-group">
            <div class = "col-md-4 col-sm-4 col-xs-12">
                <?= $form->field($model, 'type', ['template' => $fieldTempl,'labelOptions'=>['class' => 'control-label']])->dropDownList(\common\models\CUserTypes::getUserTypesMap(),[
                    'prompt' => Yii::t('app/users','Choose_user_type')
                ]) ?>
            </div>
            <div class = "col-md-4 col-sm-4 col-xs-12">
               <?= $form->field($model, 'manager_id', [
                   'template' => $fieldTempl,
                   'labelOptions'=>['class' => 'control-label']])
                   ->widget(\kartik\select2\Select2::classname(),[
                       'data' => \backend\models\BUser::getListManagers(),
                       'options' => ['placeholder' => Yii::t('app/users','Choose_manager')],
                       'pluginOptions' => [
                           'allowClear' => true
                       ],
                   ]);
               ?>
            </div>
            <div class = "col-md-4 col-sm-4 col-xs-12">
                <?= $form->field($model, 'status', ['template' => $fieldTempl,'labelOptions'=>['class' => 'control-label']])->dropDownList(\common\models\CUser::getStatusArr()) ?>
            </div>
        </div>

        <!--- Passport section--->
        <section class="passportBlock hideBlockClass">
            <div class="ln_solid"></div>
            <h4 class = "h4_ml_10">Паспортные данные</h4>
            <div class = "form-group">
                <div class = "col-md-1 col-sm-1 col-xs-6">
                    <?= $form->field($modelR, 'pasp_series', ['template' => $fieldTempl,'labelOptions'=>['class' => 'control-label']])->textInput(['maxlength' => TRUE]) ?>
                </div>
                <div class = "col-md-3 col-sm-3 col-xs-6">
                    <?= $form->field($modelR, 'pasp_number', ['template' => $fieldTempl,'labelOptions'=>['class' => 'control-label']])->textInput(['maxlength' => TRUE]) ?>
                </div>

                <div class = "col-md-4 col-sm-4 col-xs-12">
                    <?= $form->field($modelR, 'pasp_ident', ['template' => $fieldTempl,'labelOptions'=>['class' => 'control-label']])->textInput(['maxlength' => TRUE]) ?>
                </div>
                <div class = "col-md-4 col-sm-4 col-xs-12">
                    <?= $form->field($modelR, 'pasp_auth', ['template' => $fieldTempl,'labelOptions'=>['class' => 'control-label']])->textInput(['maxlength' => TRUE]) ?>
                </div>
                <div class = "col-md-4 col-sm-4 col-xs-12">
                    <?= $form->field($modelR, 'pasp_date', ['template' => $fieldTempl,'labelOptions'=>['class' => 'control-label']])->widget(DatePicker::className(), [
                        'options' => [
                            'class' => 'form-control'
                        ],
                        'pluginOptions' => [
                            'autoclose' => TRUE,
                            'format' => 'yyyy-mm-dd',
                            'defaultDate' => date('Y-m-d', time()),
                            'weekStart' => '1',
                        ]
                    ]);?>
                </div>

            </div>
        </section>
        <!---END passport section-->
        <!----Person registration info -->
        <section class="regPersonInfo hideBlockClass">
            <div class="ln_solid"></div>
            <h4 class = "h4_ml_10">Свидетельство о регистрации юр. лица(ЕГР)</h4>
            <div class = "form-group">

                    <div class = "col-md-4 col-sm-4 col-xs-12 RRB">
                    <?= $form->field($modelR, 'ynp', ['template' => $fieldTempl,'labelOptions'=>['class' => 'control-label']])->textInput(['maxlength' => TRUE]) ?>
                    </div>
                    <div class = "col-md-4 col-sm-4 col-xs-12 NORRB">
                    <?= $form->field($modelR, 'inn', ['template' => $fieldTempl,'labelOptions'=>['class' => 'control-label']])->textInput(['maxlength' => TRUE]) ?>
                </div>
                    <div class = "col-md-4 col-sm-4 col-xs-12 NORRB">
                    <?= $form->field($modelR, 'kpp', ['template' => $fieldTempl,'labelOptions'=>['class' => 'control-label']])->textInput(['maxlength' => TRUE]) ?>
                </div>
                    <div class = "col-md-4 col-sm-4 col-xs-12 NORRB">
                    <?= $form->field($modelR, 'ogrn', ['template' => $fieldTempl,'labelOptions'=>['class' => 'control-label']])->textInput(['maxlength' => TRUE]) ?>
                </div>

                <div class = "col-md-4 col-sm-4 col-xs-12">
                    <?=
                    $form->field($modelR, 'reg_date', ['template' => $fieldTempl,'labelOptions'=>['class' => 'control-label']])->widget(DatePicker::className(), [
                        'options' => [
                            'class' => 'form-control'
                        ],
                        'pluginOptions' => [
                            'autoclose' => TRUE,
                            'format' => 'yyyy-mm-dd',
                            'defaultDate' => date('Y-m-d', time()),
                            'weekStart' => '1',
                        ]
                    ]);?>
                </div>
                <div class = "col-md-4 col-sm-4 col-xs-12">
                    <?= $form->field($modelR, 'reg_number', ['template' => $fieldTempl,'labelOptions'=>['class' => 'control-label']])->textInput(['maxlength' => TRUE]) ?>
                </div>
                <div class = "col-md-4 col-sm-4 col-xs-12">
                    <?= $form->field($modelR, 'reg_auth', ['template' => $fieldTempl,'labelOptions'=>['class' => 'control-label']])->textInput(['maxlength' => TRUE]) ?>
                </div>
            </div>
        </section>
        <!-- END person registration info-->
        <!-- Bank requisites-->
        <section class="bankRequisites hideBlockClass">
            <div class="ln_solid"></div>
            <h4 class = "h4_ml_10">Банковские реквизиты</h4>
            <div class = "form-group">
                <div class = "col-md-4 col-sm-4 col-xs-12">
                    <?= $form->field($modelR, 'ch_account', ['template' => $fieldTempl,'labelOptions'=>['class' => 'control-label']])->textInput(['maxlength' => TRUE]) ?>
                </div>
                <div class = "col-md-4 col-sm-4 col-xs-12">
                    <?= $form->field($modelR, 'b_name', ['template' => $fieldTempl,'labelOptions'=>['class' => 'control-label']])->textInput(['maxlength' => TRUE]) ?>
                </div>
                <div class = "col-md-4 col-sm-4 col-xs-12">
                    <?= $form->field($modelR, 'b_code', ['template' => $fieldTempl,'labelOptions'=>['class' => 'control-label']])->textInput(['maxlength' => TRUE]) ?>
                </div>
            </div>
        </section>
        <!--- END Bank requisites ---->
        <!-- j addres info -->
        <section class="jAddress hideBlockClass">
            <div class="ln_solid"></div>
            <h4 class = "h4_ml_10">Юридический адрес</h4>
            <?= $form->field($modelR, 'j_address',['template'=>$fieldTemplSecond,'labelOptions'=>['class' => 'control-label']])->textarea() ?>
        </section>
        <!--END j address-->
        <!-- p addres info -->
        <section class="pAddress hideBlockClass">
            <div class="ln_solid"></div>
            <h4 class = "h4_ml_10">Почтовый адрес</h4>
            <?= $form->field($modelR, 'p_address',['template'=>$fieldTemplSecond,'labelOptions'=>['class' => 'control-label']])->textarea() ?>
        </section>
        <!--END p address-->
        <!-- Contact info--->
        <section class="contactInfo hideBlockClass">
            <div class="ln_solid"></div>
            <h4 class = "h4_ml_10">Контактная информация:</h4>
            <div class = "form-group">
                <div class = "col-md-4 col-sm-4 col-xs-12">
                    <?= $form->field($modelR, 'c_fname', ['template' => $fieldTempl,'labelOptions'=>['class' => 'control-label']])->textInput(['maxlength' => TRUE]) ?>
                </div>
                <div class = "col-md-4 col-sm-4 col-xs-12">
                    <?= $form->field($modelR, 'c_lname', ['template' => $fieldTempl,'labelOptions'=>['class' => 'control-label']])->textInput(['maxlength' => TRUE]) ?>
                </div>
                <div class = "col-md-4 col-sm-4 col-xs-12">
                    <?= $form->field($modelR, 'c_mname', ['template' => $fieldTempl,'labelOptions'=>['class' => 'control-label']])->textInput(['maxlength' => TRUE]) ?>
                </div>
                <div class = "col-md-4 col-sm-4 col-xs-12">
                    <?= $form->field($modelR, 'c_email', ['template' => $fieldTempl,'labelOptions'=>['class' => 'control-label']])->textInput(['maxlength' => TRUE]) ?>
                </div>
                <div class = "col-md-4 col-sm-4 col-xs-12">
                    <?= $form->field($modelR, 'c_phone', ['template' => $fieldTempl,'labelOptions'=>['class' => 'control-label']])->textInput(['maxlength' => TRUE]) ?>
                </div>
                <div class = "col-md-4 col-sm-4 col-xs-12">
                    <?= $form->field($modelR, 'c_fax', ['template' => $fieldTempl,'labelOptions'=>['class' => 'control-label']])->textInput(['maxlength' => TRUE]) ?>
                </div>
           </div>
        </section>
    <section class="contactSite">
        <?php echo $form->field($modelR,'site')->widget(\yii\widgets\MaskedInput::className(),[
            'clientOptions' => [
                'alias' =>  'url',
            ],
            'options' => [
                'placeholder'=>'http://site.com',
                'class'=>'form-control'
            ]
        ]);
        ?>
    </section>

    <div class="form-group">
        <div class = "col-md-offset-8 pull-right">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app/users', 'Create') : Yii::t('app/users', 'Update btn'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        </div>
        </div>

    <?php ActiveForm::end(); ?>

</div>
