<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 23.07.15
 */
use wbraganca\dynamicform\DynamicFormWidget;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Json;
use common\components\helpers\CustomViewHelper;
use yii\helpers\Url;
$this->title  = Yii::t('app/book','Add payment');
$sCurrn = is_object($obCur = $modelP->currency) ? $obCur->code : 'N/A';
$this->registerCssFile('@web/css/select/select2.min.css');
CustomViewHelper::registerJsFileWithDependency('@web/js/wm_app/helpers.js',$this);
CustomViewHelper::registerJsFileWithDependency('@web/js/select/select2.full.js',$this);
CustomViewHelper::registerJsFileWithDependency('@web/js/accounting/accounting.min.js',$this,[],'accounting');
CustomViewHelper::registerJsFileWithDependency('@web/js/parts/add_payment.js',$this,['accounting']);
$this->registerJs("
var
    sCurrn = '".$sCurrn."',
    addPErrorTitle = '".Yii::t('app/book','Error')."',
    addPErrorTitleCond = '".Yii::t('app/book','Condition request')."',
    addPErrorTitleBoundCheckCond = '".Yii::t('app/book','Bounds checking conditions request')."',
    addPErrorTitleCheckIsSale = '".Yii::t('app/book','Check is sale')."',
    addPErrorTextFullAmount = '".Yii::t('app/book','You have to spend all amout')."',
    addPErrorTextFullSetAmountAndService = '".Yii::t('app/book','You must set amount and choose service')."',
    addPErrorTextServerErr = '".Yii::t('app/book','Server error')."',
    addPErrorTextBoundCheckCond = '".Yii::t('app/book','Bounds checking conditions FAIL')."',
    urlFindCondition = '".Url::to(['find-condition'])."',
    urlBoundsCheckingConditions = '".Url::to(['bounds-checking-conditions'])."',
    urlIsSale = '".Url::to(['is-sale'])."',
    iLegalPersonId = ".$modelP->legal_id.",
    iContractorId = ".$modelP->cntr_id.",
    iPaymentRequestId = ".$modelP->id.",
    iPayCondTypeUsual = ".\common\models\PaymentCondition::TYPE_USUAL.",
    iPayCondTypeCustom = ".\common\models\PaymentCondition::TYPE_CUSTOM.",
    iCurrencyId = ".$modelP->currency_id.",
    sPayDate = '".$modelP->pay_date."',
    conditions = ".\yii\helpers\Json::encode(\common\models\PaymentCondition::getConditionWithCurrency(date('Y-m-d',$modelP->pay_date))).",
    keys = ".\yii\helpers\Json::encode(array_keys(\common\models\PaymentCondition::getConditionMap())).",
    condIdVisible = ".\yii\helpers\Json::encode($arCondVisible).",
    condTypeMap = ".\yii\helpers\Json::encode(\common\models\PaymentCondition::getConditionTypeMap())."
    ;
",\yii\web\View::POS_HEAD);

?>
<div class="payments-form">
    <?php $form = ActiveForm::begin([
        'id' => 'dynamic-form',
        'options' => [
            'class' => 'form-horizontal form-label-left'
        ],
        'fieldConfig' => [
            'template' => '{label}<div class="col-md-10 col-sm-10 col-xs-12">{input}</div><ul class="parsley-errors-list" >{error}</ul>',
            'labelOptions' => ['class' => 'control-label col-md-2 col-sm-2 col-xs-12'],
        ],
    ]); ?>
    <?php DynamicFormWidget::begin([
        'widgetContainer' => 'dynamicform_wrapper', // required: only alphanumeric characters plus "_" [A-Za-z0-9_]
        'widgetBody' => '.container-items', // required: css class selector
        'widgetItem' => '.item', // required: css class
        'limit' => 10, // the maximum times, an element can be cloned (default 999)
        'min' => 1, // 0 or 1 (default 1)
        'insertButton' => '.add-item', // css class
        'deleteButton' => '.remove-item', // css class
        'model' => $model[0],
        'formId' => 'dynamic-form',
        'formFields' => [
            'service',
            'summ',
            'comment'
        ],
    ]); ?>
    <div class="row">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2><?= Html::encode($this->title) ?></h2>
                    <section class="pull-right">
                        <?= Html::button('<i class="glyphicon glyphicon-plus" ></i> '.Yii::t('app/book','Add new payment'),['class' => 'add-item btn btn-success'])?>
                        <?= Html::submitButton(Yii::t('app/book','Save'), ['class' => 'btn btn-primary']) ?>
                        <?= Html::a(Yii::t('app/book', 'To list'), ['index'], ['class' => 'btn btn-warning']) ?>
                    </section>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content container-items">
                    <div class="row">
                        <div class="col-md-6 col-sm-6 col-xs-12">
                             <?php
                                echo \yii\widgets\DetailView::widget([
                                     'model' => $modelP,
                                     'options' => [
                                         'class' => 'table table-bordered'
                                     ],
                                     'attributes' => [
                                         [
                                             'attribute' => 'cntr_id',
                                             'value' => is_object($obCuser = $modelP->cuser) ? $obCuser->getInfo() : 'N/A'
                                         ],
                                         [
                                             'attribute' => 'legal_id',
                                             'value' => is_object($obLegal = $modelP->legal) ? $obLegal->name : 'N/A'
                                         ],
                                         [
                                              'attribute' => 'pay_summ',
                                              'value' => Yii::$app->formatter->asDecimal($modelP->pay_summ).' '.$sCurrn
                                         ],
                                         [
                                             'attribute' => 'pay_date',
                                             'value' => Yii::$app->formatter->asDate($modelP->pay_date)
                                         ],
                                         'description:text'
                                     ]
                                ])?>
                        </div>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <?php echo Html::hiddenInput('availableSumm',$modelP->pay_summ,['id' => 'aSumm'])?>
                            <h3><?php echo Yii::t('app/book','AvailableSumm');?>: <small id="aSummDispl"><?php echo $modelP->pay_summ; ?></small></h3>
                            <p class="warning-attention"><?=Yii::t('app/book','Attention. Unit will be enroll for payment request owner!')?></p>
                        </div>
                    </div>
                    <?php foreach ($model as $i => $m): ?>
                        <div class="item col-md-6 col-sm-6 col-xs-12">
                            <div class="x_panel">
                                <div class="x_title">
                                    <h2><i class="fa fa-align-left"></i> <?php echo Yii::t('app/book','Payment');?></h2>
                                    <ul class="nav navbar-right">
                                        <li>
                                            <a class="remove-item"><i class="glyphicon glyphicon-remove" style="color:red;cursor: pointer;"></i></a>
                                        </li>
                                    </ul>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="x_content">
                                    <div class="panel-body">
                                        <?= Html::activeHiddenInput($m, "[{$i}]condType")?>
                                        <?= $form->field($m, "[{$i}]service")->dropDownList(
                                            \common\models\Services::getServicesMap(),[
                                            'prompt' => Yii::t('app/book','Choose service'),
                                            'onchange' => 'findCondition(this);isSaleCheck(this);',
                                            'data-service-id' => $i
                                        ]) ?>
                                        <?= $form->field($m, "[{$i}]summ")->textInput([
                                            'maxlength' => true,
                                            'class' => 'form-control psumm',
                                            'onchange' => 'boundsCheckingConditions(this);',
                                        ]) ?>
                                        <?= $form->field($m,"[{$i}]customProduction")->textInput([
                                            'disabled' => 'disabled'
                                        ])?>
                                        <?= $form->field($m, "[{$i}]comment")->textarea() ?>
                                        <?= $form->field($m, "[{$i}]condID")->dropDownList(\common\models\PaymentCondition::getConditionWithCurrency(date('Y-m-d',$modelP->pay_date)),[
                                            'prompt' => Yii::t('app/book','Choose condition'),
                                            'data-cond-id' => $i,
                                            'onchange' => 'boundsCheckingConditions(this);',
                                            'class' => 'form-control cond-class'
                                        ]) ?>
                                        <div class="row">
                                        <div class="col-md-offset-2 pdd-left-5">
                                            <div class="col-md-6">
                                                <?= $form->field($m,"[{$i}]hide_act_payment")->checkbox()?>
                                            </div>
                                            <div class="col-md-6">
                                            </div>
                                        </div>
                                            </div>
                                        <div class="row">
                                            <div class="col-md-offset-2 pdd-left-5">
                                                <?php if(Yii::$app->user->can('adminRights')):?>
                                                <div class="col-md-6">
                                                <?= $form->field($m,"[{$i}]showAll",[])->checkbox([
                                                    'class' => 'showAllBtn'
                                                ])?>
                                                </div>
                                                <?php endif;?>
                                                <div class="col-md-6 pdd-top-10">
                                                    <?= Html::a(Yii::t('app/book','Condition info'),'http://wiki.webmart.by/pages/viewpage.action?pageId=2556180',[
                                                        'target' => 'blank'
                                                    ])?>
                                                </div>

                                            </div>
                                        </div>
                                        <div class="row maybesale <?php if(!$m->isSale)echo 'hide';?>">
                                            <hr/>
                                            <div class="col-md-offset-2 pdd-left-15">
                                            <span class="warning "><?=Yii::t('app/book','Maybe payment is sale');?></span>
                                             </div>
                                            <div>
                                                <div class="col-md-3 col-md-offset-2 pdd-left-15">
                                                    <?= $form->field($m,"[{$i}]isSale",[])->checkbox([
                                                        'class' => ''
                                                    ])?>
                                                </div>
                                                <div class="col-md-7" >
                                                        <?=$form->field($m,"[{$i}]saleUser",[
                                                                'template' => '<div class="col-md-12 col-sm-12 col-xs-12">{input}</div><ul class="parsley-errors-list" >{error}</ul>',
                                                                //'labelOptions' => ['class' => 'control-label col-md-4 col-sm-4 col-xs-12'],
                                                            ])
                                                            ->dropDownList(\backend\models\BUser::getAllMembersMap(),[
                                                                'class' => 'selectDrop',
                                                                'prompt' => Yii::t('app/book','Choose user')
                                                            ])?>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <?php DynamicFormWidget::end(); ?>
    <?php ActiveForm::end(); ?>
</div>