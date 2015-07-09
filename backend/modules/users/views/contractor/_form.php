<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\jui\DatePicker;

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
        }else{
            rrb.show();
            norrb.hide();
        }
}

',\yii\web\View::POS_END);

$this->registerJs('
modeResident();
$("#cuser-is_resident").on("change",modeResident);
',\yii\web\View::POS_READY);


?>

<div class = "x_content">
    <br />
    <?php $form = ActiveForm::begin([
        'options' => [
            'class' => 'form-horizontal form-label-left'
        ],
        'fieldConfig' => [
            'template' => '<div class="form-group">{label}<div class="col-md-6 col-sm-6 col-xs-12">{input}</div><ul class="parsley-errors-list" >{error}</ul></div>',
            'labelOptions' => ['class' => 'control-label col-md-3 col-sm-3 col-xs-12'],
        ],
    ]); ?>

    <?= $form->field($model, 'username')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'ext_id')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'manager_id')->dropDownList(\backend\models\BUser::getListManagers(),[
        'prompt' => Yii::t('app/users','Choose_manager')
    ]) ?>

    <?= $form->field($model, 'status')->dropDownList(\common\models\CUser::getStatusArr()) ?>

    <?= $form->field($model, 'type')->dropDownList(\common\models\CUserTypes::getUserTypesMap(),[
        'prompt' => Yii::t('app/users','Choose_user_type')
    ]) ?>

    <?= $form->field($model,'is_resident')->dropDownList(\common\models\CUser::getResidentArr());?>

    <?= $form->field($model,'r_country')->textInput(['maxlength' => true])?>

    <?php if($model->isNewRecord):?>
        <h4><?php echo Yii::t('app/users','Requisites');?></h4>
        <div class="ln_solid"></div>
        <h4>Сведение о юридическом лице</h4>
        <?= $form->field($modelR,'corp_name',['template'=>$fieldTemplSecond,'labelOptions'=>['class' => 'control-label']])->textInput(['maxlength' => true])?>

        <h4>Лицо, уполномоченное на заключение договора</h4>
        <div class="form-group">
            <div class="col-md-4 col-sm-4 col-xs-12">
                <?= $form->field($modelR,'j_fname',['template'=>$fieldTempl,'labelOptions'=>['class' => 'control-label']])->textInput(['maxlength' => true])?>
            </div>
            <div class="col-md-4 col-sm-4 col-xs-12">
                <?= $form->field($modelR,'j_lname',['template'=>$fieldTempl,'labelOptions'=>['class' => 'control-label']])->textInput(['maxlength' => true])?>
            </div>
            <div class="col-md-4 col-sm-4 col-xs-12">
                <?= $form->field($modelR,'j_mname',['template'=>$fieldTempl,'labelOptions'=>['class' => 'control-label']])->textInput(['maxlength' => true])?>
            </div>
            <div class="col-md-4 col-sm-4 col-xs-12">
                <?= $form->field($modelR,'j_post',['template'=>$fieldTempl,'labelOptions'=>['class' => 'control-label']])->textInput(['maxlength' => true])?>
            </div>
            <div class="col-md-4 col-sm-4 col-xs-12">
                <?= $form->field($modelR,'j_doc',['template'=>$fieldTempl,'labelOptions'=>['class' => 'control-label']])->textInput(['maxlength' => true])?>
            </div>
        </div>

        <h4>Свидетельство о регистрации юр. лица(ЕГР)</h4>
        <div class="form-group">
            <div class="col-md-4 col-sm-4 col-xs-12 RRB">
                <?= $form->field($modelR,'ynp',['template'=>$fieldTempl,'labelOptions'=>['class' => 'control-label']])
                    ->textInput([
                        'maxlength' => true
                    ])?>
            </div>
            <div class="col-md-4 col-sm-4 col-xs-12 RRB">
                <?= $form->field($modelR,'okpo',['template'=>$fieldTempl,'labelOptions'=>['class' => 'control-label']])
                    ->textInput([
                        'maxlength' => true,
                    ])?>
            </div>
            <div class="col-md-4 col-sm-4 col-xs-12 NORRB">
                <?= $form->field($modelR,'inn',['template'=>$fieldTempl,'labelOptions'=>['class' => 'control-label']])
                    ->textInput([
                        'maxlength' => true
                    ])?>
            </div>
            <div class="col-md-4 col-sm-4 col-xs-12 NORRB">
                <?= $form->field($modelR,'kpp',['template'=>$fieldTempl,'labelOptions'=>['class' => 'control-label']])
                    ->textInput([
                        'maxlength' => true
                    ])?>
            </div>
            <div class="col-md-4 col-sm-4 col-xs-12 NORRB">
                <?= $form->field($modelR,'ogrn',['template'=>$fieldTempl,'labelOptions'=>['class' => 'control-label']])
                    ->textInput([
                        'maxlength' => true
                    ])?>
            </div>
            <div class="col-md-4 col-sm-4 col-xs-12">
                <?= $form->field($modelR,'reg_date',['template'=>$fieldTempl,'labelOptions'=>['class' => 'control-label']])->widget(DatePicker::className(),[
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
                <?= $form->field($modelR,'reg_number',['template'=>$fieldTempl,'labelOptions'=>['class' => 'control-label']])->textInput(['maxlength' => true])?>
            </div>
            <div class="col-md-4 col-sm-4 col-xs-12">
                <?= $form->field($modelR,'reg_auth',['template'=>$fieldTempl,'labelOptions'=>['class' => 'control-label']])->textInput(['maxlength' => true])?>
            </div>
        </div>
        <h4>Банковские реквизиты</h4>
        <div class="form-group">
            <div class="col-md-4 col-sm-4 col-xs-12">
                <?= $form->field($modelR,'ch_account',['template'=>$fieldTempl,'labelOptions'=>['class' => 'control-label']])->textInput(['maxlength' => true])?>
            </div>
            <div class="col-md-4 col-sm-4 col-xs-12">
                <?= $form->field($modelR,'b_name',['template'=>$fieldTempl,'labelOptions'=>['class' => 'control-label']])->textInput(['maxlength' => true])?>
            </div>
            <div class="col-md-4 col-sm-4 col-xs-12">
                <?= $form->field($modelR,'b_code',['template'=>$fieldTempl,'labelOptions'=>['class' => 'control-label']])->textInput(['maxlength' => true])?>
            </div>
        </div>

        <h4>Юридический адрес</h4>
        <?= $form->field($modelR,'j_address',['template'=>$fieldTemplSecond,'labelOptions'=>['class' => 'control-label']])->textarea()?>
        <h4>Почтовый адрес</h4>
        <?= $form->field($modelR,'p_address',['template'=>$fieldTemplSecond,'labelOptions'=>['class' => 'control-label']])->textarea()?>

        <h4>Контактная информация:</h4>
        <div class="form-group">
            <div class="col-md-4 col-sm-4 col-xs-12">
                <?= $form->field($modelR,'c_fname',['template'=>$fieldTempl,'labelOptions'=>['class' => 'control-label']])->textInput(['maxlength' => true])?>
            </div>
            <div class="col-md-4 col-sm-4 col-xs-12">
                <?= $form->field($modelR,'c_lname',['template'=>$fieldTempl,'labelOptions'=>['class' => 'control-label']])->textInput(['maxlength' => true])?>
            </div>
            <div class="col-md-4 col-sm-4 col-xs-12">
                <?= $form->field($modelR,'c_mname',['template'=>$fieldTempl,'labelOptions'=>['class' => 'control-label']])->textInput(['maxlength' => true])?>
            </div>
            <div class="col-md-4 col-sm-4 col-xs-12">
                <?= $form->field($modelR,'c_email',['template'=>$fieldTempl,'labelOptions'=>['class' => 'control-label']])->textInput(['maxlength' => true])?>
            </div>
            <div class="col-md-4 col-sm-4 col-xs-12">
                <?= $form->field($modelR,'c_phone',['template'=>$fieldTempl,'labelOptions'=>['class' => 'control-label']])->textInput(['maxlength' => true])?>
            </div>
            <div class="col-md-4 col-sm-4 col-xs-12">
                <?= $form->field($modelR,'c_fax',['template'=>$fieldTempl,'labelOptions'=>['class' => 'control-label']])->textInput(['maxlength' => true])?>
            </div>
       </div>
    <?php endif;?>
    <div class="form-group">
        <div class = "col-md-offset-8 pull-right">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app/users', 'Create') : Yii::t('app/users', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        </div>
        </div>

    <?php ActiveForm::end(); ?>

</div>
