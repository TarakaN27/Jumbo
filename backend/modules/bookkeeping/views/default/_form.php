<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\jui\DatePicker;
use kartik\select2\Select2;
use common\components\helpers\CustomViewHelper;
use yii\helpers\Url;
/* @var $this yii\web\View */
/* @var $model common\models\Payments */
/* @var $form yii\widgets\ActiveForm */
$fieldTpl = '<div>{input}</div><ul class="parsley-errors-list" >{error}</ul>';
CustomViewHelper::registerJsFileWithDependency('@web/js/wm_app/helpers.js',$this);
CustomViewHelper::registerJsFileWithDependency('@web/js/accounting/accounting.min.js',$this,[],'accounting');
CustomViewHelper::registerJsFileWithDependency('@web/js/parts/update_payment.js',$this,['accounting']);
$this->registerJsFile('@web/js/php_functions/strtotime.js',['position' => \yii\web\View::POS_HEAD]);
$this->registerJs("
var
    arCondTypes = '.\yii\helpers\Json::encode(\common\models\PaymentCondition::getConditionTypeMap()).'
    urlFindCondition = '".Url::to(['find-condition'])."',
    urlBoundsCheckingConditions = '".Url::to(['/bookkeeping/payment-request/bounds-checking-conditions'])."',
    urlGetCondition = '".Url::to(['get-conditions'])."',
    titleCondFind = '".Yii::t('app/book','Condition request')."',
    errorCondFind = '".Yii::t('app/book','Server error')."',
    addPErrorTextServerErr = '".Yii::t('app/book','Server error')."',
    titleBoundsCheck = '".Yii::t('app/book','Bounds checking conditions request')."',
    errorBoundsCheck = '".Yii::t('app/book','Bounds checking conditions FAIL')."',
    conditions = ".\yii\helpers\Json::encode(\common\models\PaymentCondition::getConditionWithCurrency(date('Y-m-d',$model->pay_date))).",
    keys = ".\yii\helpers\Json::encode(array_keys(\common\models\PaymentCondition::getConditionMap())).",
    selectedCondVal = ".\yii\helpers\Json::encode($model->condition_id).",
    arCondIdVisible = ".\yii\helpers\Json::encode($arCondVisible)."
    ;


",\yii\web\View::POS_HEAD);

$this->registerJs('
     

',\yii\web\View::POS_READY);

?>

<div class="payments-form">

    <?php $form = ActiveForm::begin([
        'options' => [
            'class' => 'form-horizontal form-label-left'
        ],
        'fieldConfig' => [
            'template' => '{label}<div class="col-md-6 col-sm-6 col-xs-12">{input}</div><ul class="parsley-errors-list" >{error}</ul>',
            'labelOptions' => ['class' => 'control-label col-md-3 col-sm-3 col-xs-12'],
        ],
    ]); ?>

    <?php  echo $form->field($model, 'cuser_id')->widget(Select2::classname(), [
        'data' => \common\models\CUser::getContractorMap(),
        'options' => [
            'placeholder' => Yii::t('app/book','BOOK_choose_cuser')
        ],
        'pluginOptions' => [
            'allowClear' => true
        ],
        ]); ?>

    <?= $form->field($model, 'pay_date')->widget(DatePicker::className(),[
        'dateFormat' => 'dd.MM.yyyy',
        'clientOptions' => [
            'defaultDate' => date('d.m.Y',time())
        ],
        'options' => [
            'class' => 'form-control'
        ]
    ]) ?>

    <div class="form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="payments-service_id"><?php echo Html::activeLabel($model,'pay_summ');?></label>
        <div class='col-md-6 col-sm-6 col-xs-12'>
            <?= $form->field($model, 'pay_summ',['template' => $fieldTpl,'options' => [
                'class' => 'col-md-8 col-sm-8 col-xs-12',
                'style' => 'padding-left:0px;'

            ]])
                ->textInput([
                    'maxlength' => true,
                    'onchange' => 'boundsCheckingConditions();'
                ])->label(false)
            ?>
            <?= $form->field($model, 'currency_id',['template' => $fieldTpl,'options' => [
                'class' => 'col-md-4 col-sm-4 col-xs-12',
                'style' => 'padding-right:0px;'
            ]])
                ->dropDownList(\common\models\ExchangeRates::getRatesCodes())->label(false) ?>
        </div>
    </div>
    <?= $form->field($model,'customProd')->textInput()?>

    <?= $form->field($model, 'service_id')->dropDownList(\common\models\Services::getServicesMap(),[
        'prompt' => Yii::t('app/book','BOOK_choose_service'),
        'onchange' => 'findCondition()'
    ]) ?>

    <?= $form->field($model, 'legal_id')->dropDownList(\common\models\LegalPerson::getLegalPersonMap(),[
        'onchange' => 'findCondition();'
    ]) ?>

    <?= $form->field($model, 'condition_id')->dropDownList([],[
        'prompt' => Yii::t('app/book','BOOK_choose_payment_condition'),
        'onchange' => 'boundsCheckingConditions();'
    ]) ?>
    <div class="row">
        <div class="col-md-offset-3 pdd-left-15">
            <?php if(Yii::$app->user->can('adminRights')):?>
            <div class="col-md-6">
                <?= $form->field($model,"showAll")->checkbox([
                    'class' => 'showAllBtn',
                    'id' => 'show_all_id'
                ])?>
            </div>
            <?php endif;?>
            <div class="col-md-6">
                <?= Html::a(Yii::t('app/book','Condition info'),'http://wiki.webmart.by/pages/viewpage.action?pageId=2556180',[
                    'target' => 'blank'
                ])?>
            </div>
        </div>
    </div>

    <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

    <div class="form-group">
        <div class = "col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
            <?php if(!$model->isNewRecord):?>
                <?=$form->field($model,'updateWithNewCondition')->checkbox();?>
            <?php endif;?>
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app/book', 'Create') : Yii::t('app/book', 'Update btn'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div></div>

    <?php ActiveForm::end(); ?>

</div>
