<?php
use yii\bootstrap\ActiveForm;
use kartik\date\DatePicker;
use kartik\select2\Select2;
use yii\helpers\Html;
use common\components\helpers\CustomViewHelper;
use yii\helpers\Url;

$fieldTpl = '<div>{input}</div><ul class="parsley-errors-list" >{error}</ul>';
CustomViewHelper::registerJsFileWithDependency('@web/js/accounting/accounting.min.js',$this,[],'accounting');
CustomViewHelper::registerJsFileWithDependency('@web/js/parts/payment_create_request.js',$this,['accounting']);
CustomViewHelper::registerJsFileWithDependency('@web/js/parts/act_payments-request.js',$this,['accounting']);
$this->registerJs("
    var
        urlGetManager = '".\yii\helpers\Url::to(['default/get-manager'])."',
        URL_LOAD_ACTS_REQUEST_PAYMENTS = '".Url::to(['/ajax-service/find-request-payments-for-acts'])."';
        errorTitle = '".Yii::t('app/common','Error')."',
        errorText = '".Yii::t('app/common','Can not load manager for contractor')."'
        ;
",\yii\web\View::POS_HEAD);
?>
<?php $form = ActiveForm::begin([
    'options' => [
        'class' => 'form-horizontal form-label-left form-payment-request',
        'id'=>'emptyForm'
    ],
    'fieldConfig' => [
        'template' => '{label}<div class="col-md-6 col-sm-6 col-xs-12">{input}</div><ul class="parsley-errors-list" >{error}</ul>',
        'labelOptions' => ['class' => 'control-label col-md-3 col-sm-3 col-xs-12'],
    ],
    'action' => 'create-empty-payment-request',
]); ?>

<?php  echo $form->field($modelEmpty, 'cntr_id')->widget(Select2::classname(), [
    'data' => \common\models\CUser::getContractorMap(),
    'options' => ['placeholder' => Yii::t('app/book','BOOK_choose_cuser')],
    'pluginOptions' => [
        'allowClear' => true,
    ],
]); ?>

<?php echo $form->field($modelEmpty,'is_unknown')->dropDownList(\common\models\PaymentRequest::getYesNo());?>
<?php echo $form->field($modelEmpty,'user_name',['options' => ['class' => 'form-group hide']])->textInput();?>

<?php  echo $form->field($modelEmpty, 'manager_id')->widget(Select2::classname(), [
    'data' => \backend\models\BUser::getAllMembersMap(),
    'options' => ['placeholder' => Yii::t('app/book','BOOK_choose_managers')],
    'pluginOptions' => [
        'allowClear' => true,
    ],
]); ?>


<?= $form->field($modelEmpty, 'pay_date')->widget(DatePicker::className(),[
    'options' => [
        'class' => 'form-control'
    ],
    'pluginOptions' => [
        'autoclose' => TRUE,
        'format' =>'dd.mm.yyyy', //'yyyy-mm-dd',
        'defaultDate' => date('d.m.Y', time()),
        'weekStart' => '1',
    ]
]) ?>

<div class="form-group">
    <label class="control-label col-md-3 col-sm-3 col-xs-12" for="payments-service_id"><?php echo Html::activeLabel($modelEmpty,'pay_summ');?></label>
    <div class='col-md-6 col-sm-6 col-xs-12'>
        <?= $form->field($modelEmpty, 'pay_summ',['template' => $fieldTpl,'options' => [
            'class' => 'col-md-8 col-sm-8 col-xs-12',
            'style' => 'padding-left:0px;'
        ]])
            ->textInput(['maxlength' => true])->label(false) ?>
        <?= $form->field($modelEmpty, 'currency_id',['template' => $fieldTpl,'options' => [
            'class' => 'col-md-4 col-sm-4 col-xs-12',
            'style' => 'padding-right:0px;'
        ]])
            ->dropDownList(\common\models\ExchangeRates::getRatesCodes())->label(false) ?>
    </div>
</div>


<?= $form->field($modelEmpty, 'legal_id')->dropDownList(\common\models\LegalPerson::getLegalPersonMap()) ?>

<?foreach(\common\models\LegalPerson::getLegalPersonForBill() as $legalPerson){?>
    <?
    if($modelEmpty->legal_id == $legalPerson->id)
        $modelEmpty->bank[$legalPerson->id] = $modelEmpty->bank_id;
    else
        $modelEmpty->bank[$legalPerson->id] = $legalPerson->default_bank_id;
        ?>
    <div style="display: none;" class="legal_banks" id = "bank<?=$legalPerson->id?>">
    <?= $form->field($modelEmpty, "bank[$legalPerson->id]")->dropDownList($legalPerson->getDefaultBankDetailsMap());?>
    </div>
<?}?>
<p style="display:none; text-align:center; color:red" id="invalidBank">Банковские реквизиты платежа не соответствуют заданным для контагента</p>

<?= $form->field($modelEmpty, 'service_id')->dropDownList(\common\models\Services::getServicesMap(),[
    'prompt' => Yii::t('app/book','Choose service')
])?>

<?= $form->field($modelEmpty, 'description')->textarea(['rows' => 6]) ?>

<div class="form-group">
                <div class = "col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                <?= Html::submitButton($modelEmpty->isNewRecord ? Yii::t('app/book', 'Create') : Yii::t('app/book', 'Update'), ['class' => $modelEmpty->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
                </div>
            </div>

<?php ActiveForm::end(); ?>

