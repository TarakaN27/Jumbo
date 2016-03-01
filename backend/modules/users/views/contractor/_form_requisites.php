<?php

use kartik\date\DatePicker;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\CUser */
/* @var $form yii\widgets\ActiveForm */
$fieldTempl = '<div>{label}{input}</div><ul class="parsley-errors-list" >{error}</ul>';
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
            'template' => '<div class="form-group"><div class="col-md-6 col-sm-6 col-xs-12">{label}{input}<ul class="parsley-errors-list" >{error}</ul></div></div>',
            'labelOptions' => ['class' => 'control-label'],
        ],
    ]);
    echo Html::activeHiddenInput($model,'isResident');
    ?>
        <?= $form->field($model, 'type_id')->radioList(\common\models\CUserRequisites::getTypeArr(),['class'=>'radioType']) ?>

        <section class="jPersonInfo hideBlockClass">
            <div class="ln_solid"></div>
            <h4 class = "h4_ml_10">Сведение о юридическом лице</h4>
            <?= $form->field($model, 'corp_name')->textInput(['maxlength' => TRUE]) ?>
        </section>

        <div class="ln_solid"></div>
        <h4 class = "h4_ml_10">Лицо, уполномоченное на заключение договора</h4>
        <div class = "form-group">
            <div class = "col-md-4 col-sm-4 col-xs-12">
                <?= $form->field($model, 'j_lname', ['template' => $fieldTempl])->textInput(['maxlength' => TRUE]) ?>
            </div>
            <div class = "col-md-4 col-sm-4 col-xs-12">
                <?= $form->field($model, 'j_fname', ['template' => $fieldTempl])->textInput(['maxlength' => TRUE]) ?>
            </div>
            <div class = "col-md-4 col-sm-4 col-xs-12">
                <?= $form->field($model, 'j_mname', ['template' => $fieldTempl])->textInput(['maxlength' => TRUE]) ?>
            </div>
            <div class = "col-md-4 col-sm-4 col-xs-12 jPersonInfo hideBlockClass">
                <?= $form->field($model, 'j_post', ['template' => $fieldTempl])->textInput(['maxlength' => TRUE]) ?>
            </div>
            <div class = "col-md-4 col-sm-4 col-xs-12 jPersonInfo hideBlockClass">
                <?= $form->field($model, 'j_doc', ['template' => $fieldTempl])->textInput(['maxlength' => TRUE]) ?>
            </div>
        </div>
        <!--- Passport section--->
        <section class="passportBlock hideBlockClass">
            <div class="ln_solid"></div>
            <h4 class = "h4_ml_10">Паспортные данные</h4>
            <div class = "form-group">
                <div class = "col-md-1 col-sm-1 col-xs-6">
                    <?= $form->field($model, 'pasp_series', ['template' => $fieldTempl])->textInput(['maxlength' => TRUE]) ?>
                </div>
                <div class = "col-md-3 col-sm-3 col-xs-6">
                    <?= $form->field($model, 'pasp_number', ['template' => $fieldTempl])->textInput(['maxlength' => TRUE]) ?>
                </div>

                <div class = "col-md-4 col-sm-4 col-xs-12">
                    <?= $form->field($model, 'pasp_ident', ['template' => $fieldTempl])->textInput(['maxlength' => TRUE]) ?>
                </div>
                <div class = "col-md-4 col-sm-4 col-xs-12">
                    <?= $form->field($model, 'pasp_auth', ['template' => $fieldTempl])->textInput(['maxlength' => TRUE]) ?>
                </div>
                <div class = "col-md-4 col-sm-4 col-xs-12">
                    <?= $form->field($model, 'pasp_date', ['template' => $fieldTempl])->widget(DatePicker::className(), [
                        'options' => [
                            'class' => 'form-control'
                        ],
                        'pluginOptions' => [
                            'autoclose' => TRUE,
                            'format' => 'yyyy-mm-dd',
                            'defaultDate' => date('Y-m-d', time())
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
                <?php if($modelU->is_resident == \common\models\CUser::RESIDENT_YES): ?>
                    <div class = "col-md-4 col-sm-4 col-xs-12">
                    <?= $form->field($model, 'ynp', ['template' => $fieldTempl])->textInput(['maxlength' => TRUE]) ?>
                </div>

                <?php else: ?>
                    <div class = "col-md-4 col-sm-4 col-xs-12">
                    <?= $form->field($model, 'inn', ['template' => $fieldTempl])->textInput(['maxlength' => TRUE]) ?>
                </div>
                    <div class = "col-md-4 col-sm-4 col-xs-12">
                    <?= $form->field($model, 'kpp', ['template' => $fieldTempl])->textInput(['maxlength' => TRUE]) ?>
                </div>
                    <div class = "col-md-4 col-sm-4 col-xs-12">
                    <?= $form->field($model, 'ogrn', ['template' => $fieldTempl])->textInput(['maxlength' => TRUE]) ?>
                </div>
                <?php endif; ?>
                <div class = "col-md-4 col-sm-4 col-xs-12">
                    <?=
                    $form->field($model, 'reg_date', ['template' => $fieldTempl])->widget(DatePicker::className(), [
                        'options' => [
                            'class' => 'form-control'
                        ],
                        'pluginOptions' => [
                            'autoclose' => TRUE,
                            'format' => 'yyyy-mm-dd',
                            'defaultDate' => date('Y-m-d', time())
                        ]
                    ]);?>
                </div>
                <div class = "col-md-4 col-sm-4 col-xs-12">
                    <?= $form->field($model, 'reg_number', ['template' => $fieldTempl])->textInput(['maxlength' => TRUE]) ?>
                </div>
                <div class = "col-md-4 col-sm-4 col-xs-12">
                    <?= $form->field($model, 'reg_auth', ['template' => $fieldTempl])->textInput(['maxlength' => TRUE]) ?>
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
                    <?= $form->field($model, 'ch_account', ['template' => $fieldTempl])->textInput(['maxlength' => TRUE]) ?>
                </div>
                <div class = "col-md-4 col-sm-4 col-xs-12">
                    <?= $form->field($model, 'b_name', ['template' => $fieldTempl])->textInput(['maxlength' => TRUE]) ?>
                </div>
                <div class = "col-md-4 col-sm-4 col-xs-12">
                    <?= $form->field($model, 'b_code', ['template' => $fieldTempl])->textInput(['maxlength' => TRUE]) ?>
                </div>
            </div>
        </section>
        <!--- END Bank requisites ---->
        <!-- j addres info -->
        <section class="jAddress hideBlockClass">
            <div class="ln_solid"></div>
            <h4 class = "h4_ml_10">Юридический адрес</h4>
            <?= $form->field($model, 'j_address')->textarea() ?>
        </section>
        <!--END j address-->
        <!-- p addres info -->
        <section class="pAddress hideBlockClass">
            <div class="ln_solid"></div>
            <h4 class = "h4_ml_10">Почтовый адрес</h4>
            <?= $form->field($model, 'p_address')->textarea() ?>
        </section>
        <!--END p address-->
        <!-- Contact info--->
        <section class="contactInfo hideBlockClass">
            <div class="ln_solid"></div>
            <h4 class = "h4_ml_10">Контактная информация:</h4>
            <div class = "form-group">
                <div class = "col-md-4 col-sm-4 col-xs-12">
                    <?= $form->field($model, 'c_fname', ['template' => $fieldTempl])->textInput(['maxlength' => TRUE]) ?>
                </div>
                <div class = "col-md-4 col-sm-4 col-xs-12">
                    <?= $form->field($model, 'c_lname', ['template' => $fieldTempl])->textInput(['maxlength' => TRUE]) ?>
                </div>
                <div class = "col-md-4 col-sm-4 col-xs-12">
                    <?= $form->field($model, 'c_mname', ['template' => $fieldTempl])->textInput(['maxlength' => TRUE]) ?>
                </div>
                <div class = "col-md-4 col-sm-4 col-xs-12">
                    <?= $form->field($model, 'c_email', ['template' => $fieldTempl])->textInput(['maxlength' => TRUE]) ?>
                </div>
                <div class = "col-md-4 col-sm-4 col-xs-12">
                    <?= $form->field($model, 'c_phone', ['template' => $fieldTempl])->textInput(['maxlength' => TRUE]) ?>
                </div>
                <div class = "col-md-4 col-sm-4 col-xs-12">
                    <?= $form->field($model, 'c_fax', ['template' => $fieldTempl])->textInput(['maxlength' => TRUE]) ?>
                </div>
           </div>
        </section>
        <!--END contact info -->
    <div class = "form-group">
        <div class = "pull-right">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app/users', 'Create') : Yii::t('app/users', 'Update btn'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        </div>
        </div>

    <?php ActiveForm::end(); ?>

</div>