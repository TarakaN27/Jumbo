<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\jui\DatePicker;

/* @var $this yii\web\View */
/* @var $model common\models\CUser */
/* @var $form yii\widgets\ActiveForm */
$fieldTempl = '<div>{label}{input}</div><ul class="parsley-errors-list" >{error}</ul>'
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

        <h4>Сведение о юридическом лице</h4>
        <?= $form->field($model,'corp_name')->textInput(['maxlength' => true])?>

        <h4>Лицо, уполномоченное на заключение договора</h4>
        <div class="form-group">
            <div class="col-md-4 col-sm-4 col-xs-12">
                <?= $form->field($model,'j_fname',['template'=>$fieldTempl])->textInput(['maxlength' => true])?>
            </div>
            <div class="col-md-4 col-sm-4 col-xs-12">
                <?= $form->field($model,'j_lname',['template'=>$fieldTempl])->textInput(['maxlength' => true])?>
            </div>
            <div class="col-md-4 col-sm-4 col-xs-12">
                <?= $form->field($model,'j_mname',['template'=>$fieldTempl])->textInput(['maxlength' => true])?>
            </div>
            <div class="col-md-4 col-sm-4 col-xs-12">
                <?= $form->field($model,'j_post',['template'=>$fieldTempl])->textInput(['maxlength' => true])?>
            </div>
            <div class="col-md-4 col-sm-4 col-xs-12">
                <?= $form->field($model,'j_doc',['template'=>$fieldTempl])->textInput(['maxlength' => true])?>
            </div>
        </div>

        <h4>Свидетельство о регистрации юр. лица(ЕГР)</h4>
        <div class="form-group">
            <?php if($modelU->is_resident == \common\models\CUser::RESIDENT_YES):?>
            <div class="col-md-4 col-sm-4 col-xs-12">
                <?= $form->field($model,'ynp',['template'=>$fieldTempl])->textInput(['maxlength' => true])?>
            </div>
            <div class="col-md-4 col-sm-4 col-xs-12">
                <?= $form->field($model,'okpo',['template'=>$fieldTempl])->textInput(['maxlength' => true])?>
            </div>
            <?php else:?>
            <div class="col-md-4 col-sm-4 col-xs-12">
                <?= $form->field($model,'inn',['template'=>$fieldTempl])->textInput(['maxlength' => true])?>
            </div>
            <div class="col-md-4 col-sm-4 col-xs-12">
                <?= $form->field($model,'kpp',['template'=>$fieldTempl])->textInput(['maxlength' => true])?>
            </div>
            <div class="col-md-4 col-sm-4 col-xs-12">
                <?= $form->field($model,'ogrn',['template'=>$fieldTempl])->textInput(['maxlength' => true])?>
            </div>
            <?php endif;?>
            <div class="col-md-4 col-sm-4 col-xs-12">
                <?= $form->field($model,'reg_date',['template'=>$fieldTempl])->widget(DatePicker::className(),[
                    'dateFormat' => 'yyyy-MM-dd',
                    'clientOptions' => [
                        'defaultDate' => date('Y-m-d',time())
                    ],
                    'options' => [
                        'class' => 'form-control'
                    ]
                ]);?>
            </div>
            <div class="col-md-4 col-sm-4 col-xs-12">
                <?= $form->field($model,'reg_number',['template'=>$fieldTempl])->textInput(['maxlength' => true])?>
            </div>
            <div class="col-md-4 col-sm-4 col-xs-12">
                <?= $form->field($model,'reg_auth',['template'=>$fieldTempl])->textInput(['maxlength' => true])?>
            </div>
        </div>
        <h4>Банковские реквизиты</h4>
        <div class="form-group">
            <div class="col-md-4 col-sm-4 col-xs-12">
                <?= $form->field($model,'ch_account',['template'=>$fieldTempl])->textInput(['maxlength' => true])?>
            </div>
            <div class="col-md-4 col-sm-4 col-xs-12">
                <?= $form->field($model,'b_name',['template'=>$fieldTempl])->textInput(['maxlength' => true])?>
            </div>
            <div class="col-md-4 col-sm-4 col-xs-12">
                <?= $form->field($model,'b_code',['template'=>$fieldTempl])->textInput(['maxlength' => true])?>
            </div>
        </div>

        <h4>Юридический адрес</h4>
        <?= $form->field($model,'j_address')->textarea()?>
        <h4>Почтовый адрес</h4>
        <?= $form->field($model,'p_address')->textarea()?>

        <h4>Контактная информация:</h4>
        <div class="form-group">
            <div class="col-md-4 col-sm-4 col-xs-12">
                <?= $form->field($model,'c_fname',['template'=>$fieldTempl])->textInput(['maxlength' => true])?>
            </div>
            <div class="col-md-4 col-sm-4 col-xs-12">
                <?= $form->field($model,'c_lname',['template'=>$fieldTempl])->textInput(['maxlength' => true])?>
            </div>
            <div class="col-md-4 col-sm-4 col-xs-12">
                <?= $form->field($model,'c_mname',['template'=>$fieldTempl])->textInput(['maxlength' => true])?>
            </div>
            <div class="col-md-4 col-sm-4 col-xs-12">
                <?= $form->field($model,'c_email',['template'=>$fieldTempl])->textInput(['maxlength' => true])?>
            </div>
            <div class="col-md-4 col-sm-4 col-xs-12">
                <?= $form->field($model,'c_phone',['template'=>$fieldTempl])->textInput(['maxlength' => true])?>
            </div>
            <div class="col-md-4 col-sm-4 col-xs-12">
                <?= $form->field($model,'c_fax',['template'=>$fieldTempl])->textInput(['maxlength' => true])?>
            </div>
       </div>

    <div class="form-group">
        <div class = "pull-right">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app/users', 'Create') : Yii::t('app/users', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        </div>
        </div>

    <?php ActiveForm::end(); ?>

</div>