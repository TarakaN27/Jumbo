<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 20.5.16
 * Time: 12.29
 */
use yii\web\JsExpression;
use yii\helpers\Json;
use common\models\LegalPerson;
use yii\helpers\Url;
use yii\web\View;
use common\models\ExchangeRates;
use common\components\helpers\CustomViewHelper;
use common\models\Services;
use yii\helpers\Html;
CustomViewHelper::registerJsFileWithDependency('@web/js/accounting/accounting.min.js',$this,[],'accounting');
CustomViewHelper::registerJsFileWithDependency('@web/js/vendor/bower/html.sortable/dist/html.sortable.min.js',$this,[],'html-sortable');
CustomViewHelper::registerJsFileWithDependency('@web/js/php_functions/array_diff.js',$this,[],'array_diff');
CustomViewHelper::registerJsFileWithDependency('@web/js/php_functions/strtotime.js',$this,[],'strTotime');
CustomViewHelper::registerJsFileWithDependency('@web/js/parts/act_form_v2.js',$this,['html-sortable','array_diff','strTotime']);
CustomViewHelper::registerJsFileWithDependency('@web/js/moment.min.js',$this);
CustomViewHelper::registerJsFileWithDependency('@web/js/datepicker/daterangepicker.js',$this);
$this->registerJs("
var
    arCurrency = ".Json::encode(ExchangeRates::getExchangeRates()).",
    arServices = ".Json::encode(Services::getServicesMap()).",
    URL_CHECK_CONTRACTOR_FIELDS = '".Url::to(['check-contractor-fields'])."',
    URL_CHECK_ACT_NUMBER = '".Url::to(['check-act-number'])."',
    URL_GET_NEXT_ACT_NUMBER = '".Url::to(['get-next-act-number'])."',
    URL_LOAD_CONTRACT_DETAIL = '".Url::to(['/ajax-service/find-contract-detail'])."';
    URL_LOAD_ACTS_PAYMENTS = '".Url::to(['/ajax-service/find-payments-for-acts'])."';
",View::POS_HEAD);
?>
<div class="act-form-v2">
    <?php $form=\yii\bootstrap\ActiveForm::begin([
        'id' => 'act-form',
        'options' => [
            'class' => 'form-horizontal form-label-left',
            'enctype' => 'multipart/form-data'
        ],
        'fieldConfig' => [
            'template' => '{label}<div class="col-md-6 col-sm-6 col-xs-12">{input}</div><ul class="parsley-errors-list" >{error}</ul>',
            'labelOptions' => ['class' => 'control-label col-md-3 col-sm-3 col-xs-12'],
        ],
    ]);?>
        <?=$form->field($model,'iCUser')->widget(\kartik\select2\Select2::className(),[
            'initValueText' => $contractorInitText, // set the initial display text
            'options' => [
                'placeholder' => Yii::t('app/crm','Search for a contact ...')
            ],
            'pluginOptions' => [
                'allowClear' => true,
                'minimumInputLength' => 2,
                'ajax' => [
                    'url' => \yii\helpers\Url::to(['/ajax-select/get-contractor']),
                    'dataType' => 'json',
                    'data' => new JsExpression('function(params) { return {q:params.term}; }')
                ],
                'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                'templateResult' => new JsExpression('function(cmp_id) { return cmp_id.text; }'),
                'templateSelection' => new JsExpression('function (cmp_id) { return cmp_id.text; }'),
            ],
        ]);?>

        <?=$form->field($model,'iLegalPerson')->dropDownList(LegalPerson::getLegalPersonMap(),[
            'prompt' => Yii::t('app/book','Choose legal person')
        ])?>
        <?foreach(\common\models\LegalPerson::getLegalPersonForBill() as $legalPerson){?>
            <?$model->bank[$legalPerson->id] = $legalPerson->default_bank_id;?>
            <div style="display: none;" class="legal_banks" id = "bank<?=$legalPerson->id?>">
                <?= $form->field($model, "bank[$legalPerson->id]")->dropDownList($legalPerson->getDefaultBankDetailsMap());?>
            </div>
        <?}?>

        <?=$form->field($model,'iActNumber')->textInput();?>

        <?=$form->field($model,'actDate')->widget(\yii\jui\DatePicker::className(),[
            'language' => 'ru',
            'dateFormat' => 'dd.MM.yyyy',
            'options' => [
                'class' => 'form-control'
            ]
        ]);?>

        <?=$form->field($model,'fAmount')->textInput();?>

    <div class="form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12"><?=Yii::t('app/book','Payments block');?></label>
        <div class="col-md-6 col-sm-6 col-xs-12" >
            <div class="well" id="paymentsBlock">
            </div>
        </div>
    </div>

    <?=$form->field($model,'iCurr')->dropDownList([],[
        'prompt' => Yii::t('app/book','Choose exchange currency')
    ])?>

    <div class="form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12"><?=Yii::t('app/book','Services');?></label>
        <div class="col-md-6 col-sm-6 col-xs-12" >
            <div class="well">
                <ul class="ul-sortable" id="servicesBlock">
                </ul>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12"><?=Yii::t('app/book','Payment hide block');?></label>
        <div class="col-md-6 col-sm-6 col-xs-12" >
            <div class="well">
                <ul class="" id="hidePaymentBlock">
                </ul>
            </div>
        </div>
    </div>

    <div class="form-group">
        <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3 col-sm-offset-3" >
            <?=$form->field($model,'bCustomAct')->checkbox();?>
        </div>
    </div>
    <?=$form->field($model,'fCustomFileAct')->fileInput()?>

    <div class="form-group">
        <div class = "col-md-12 col-sm-12 col-xs-12 col-md-offset-3">
            <?= Html::submitButton(Yii::t('app/book', 'Create'), ['class' => 'btn btn-success']) ?>
        </div>
    </div>

    <?php \yii\bootstrap\ActiveForm::end();?>
</div>