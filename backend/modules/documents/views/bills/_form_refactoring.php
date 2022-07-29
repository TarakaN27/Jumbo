<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 13.6.16
 * Time: 17.47
 */

use kartik\date\DatePicker;
use yii\bootstrap\ActiveForm;
use kartik\select2\Select2;
use yii\web\JsExpression;
use common\models\LegalPerson;
use common\models\BillTemplate;
use common\models\BillDocxTemplate;
use common\components\helpers\CustomViewHelper;
use common\models\Bills;
use yii\helpers\Json;
use yii\bootstrap\Modal;
use common\models\Services;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
CustomViewHelper::registerJsFileWithDependency('@web/js/accounting/accounting.min.js',$this,[],'accounting');
CustomViewHelper::registerJsFileWithDependency('@web/js/vendor/bower/html.sortable/dist/html.sortable.min.js',$this,[],'html-sortable');
CustomViewHelper::registerJsFileWithDependency('@web/js/parts/bill_refactoring.js',$this,['html-sortable']);
$this->registerJs('
var
    arServTplOptions = '.Json::encode(BillTemplate::getBillTemplateMap()).',
    arServMap = '.Json::encode(Services::getServicesMap()).',
    urlFindDocxTpl = "'.Url::to(['find-docx-tpl']).'",
    urlFindServiceTpl = "'.Url::to(['find-service-tpl']).'",
    urlGetTplById = "'.Url::to(['get-bill-template-detail']).'"
    ;
',\yii\web\View::POS_HEAD);
$arServiceMap = Services::getServicesMap();
?>
<?php Modal::begin([
    'id' => 'activity-modal',
    'header' => '<h2>'.Yii::t('app/documents','Add services').'</h2>',
    'size' => Modal::SIZE_DEFAULT,
    'footer' => Html::button(Yii::t('app/documents','Save'),['class' => 'btn btn-info btn-sm'])
]);?>
<?php foreach ($arServiceMap as $servID => $servName):?>
    <div class="col-md-3 col-sm-3 col-xs-12">
        <label>
            <?=Html::checkbox('arTmpServ',false,['value' => $servID]);?>
            <?=$servName;?>
        </label>
    </div>
<?php endforeach;?>
<div class="clearfix"></div>
<?php Modal::end(); ?>
<div class="bills-form">
    <?php $form = ActiveForm::begin([
        'id' => 'form-bill',
        'options' => [
            'class' => 'form-horizontal form-label-left custom-form'
        ],
        'fieldConfig' => [
            'template' => '<div class="form-group">{label}<div class="col-md-8 col-sm-8 col-xs-12">{input}</div><ul class="parsley-errors-list" >{error}</ul></div>',
            'labelOptions' => ['class' => 'control-label col-md-3 col-sm-3 col-xs-12'],
        ],
    ]); ?>

    <?= $form->field($model,'iCuserId')->widget(Select2::className(),[
        'initValueText' => $cuserDesc, // set the initial display text
        'options' => [
            'placeholder' => Yii::t('app/crm','Search for a company ...')
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
    ]) ?>
    
    <?= $form->field($model,'iLegalPerson')->dropDownList(LegalPerson::getLegalPersonMapForBill(),[
        'placeholder' => Yii::t('app/documents','Choose legal person')
    ]);?>

    <?= $form->field($model, 'iDocxTpl')->dropDownList(BillDocxTemplate::getBillDocxMap()) ; ?>

    <?= $form->field($model,'bUseTax')->dropDownList(Bills::getYesNo())?>

    <?= $form->field($model,'bTaxRate')->textInput(['maxlength' => true])?>

    <?= $form->field($model, 'sBayTarget')->textInput(['maxlength' => true]) ?>

    <? if(in_array(Yii::$app->getUser()->identity->role, [15,25])): ?>
        <?= $form->field($model, "sPayDate")->widget(DatePicker::className(),[
            'options' => [
                'class' => 'form-control'
            ],
            'pluginOptions' => [
                'autoclose' => TRUE,
                'format' =>'yyyy-mm-dd', //'yyyy-mm-dd',
                'defaultDate' => date('Y-m-d', time()),
                'weekStart' => '1',
            ]
        ]) ?>
    <? endif; ?>
	
	<?= $form->field($model, "sPeriodDate")->widget(DatePicker::className(),[
			'language' => 'ru',
            'options' => [
                'class' => 'form-control'
            ],
            'pluginOptions' => [
				'language' => 'fr',
                'autoclose' => TRUE,
                'format' =>'yyyy-mm', //'yyyy-mm',
                'defaultDate' => date('Y-m', time()),
                'weekStart' => '1',
				'minViewMode' => 1,
            ]
        ]) ?>

    <div class="form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12"><?=Yii::t('app/book','Services');?></label>
        <div class="col-md-8 col-sm-8 col-xs-12" >
            <div class="text-center">
                <?=\yii\helpers\Html::button('<i class="fa fa-plus-square"></i>',['class' => 'btn btn-info btn-xs','id' => 'addServId'])?>
            </div>
            <div class="well">
                <div class="servPreloader loader mrg-auto hide"></div>
                <ul class="ul-sortable" id="servicesBlock">
                    <?php if(count($model->arServices) > 0)foreach ($model->arServices as $key => $serviceId):?>
                        <li id="s<?=$serviceId?>" class="block-sortable" >
                            <h4><?=$arServiceMap[$serviceId];?>
                                <a href="#nogo" data-toggle="tooltip" data-placement="top" data-original-title="Удалить услугу" class="pull-right red  marg-l-10">
                                    <i class="fa fa-minus" data-serv="<?=$serviceId;?>"></i>
                                </a>
                                <a href="#nogo" data-id="<?=$serviceId;?>" data-choose="0" data-toggle="tooltip" data-placement="top" data-original-title="Выбрать описание и договор" class="pull-right red chooseService">
                                    <i class="fa fa-check"></i>
                                </a>
                            </h4>
                            <?=Html::hiddenInput('BillForm[arServices][]',$serviceId,['class' => 'arServClass'])?>
                            <?=Html::hiddenInput('BillForm[arServOrder]['.$serviceId.']',$key,['class' => 'service-order'])?>

                            <div class="form-group col-md-6 col-sm-6 col-xs-12">
                                <label class="control-label">Сумма</label>
                                <?=Html::textInput('BillForm[arServAmount]['.$serviceId.']"',$model->arServAmount[$serviceId],[
                                    'class' => 'form-control serv-amount',
                                    'data-serv-id' => $serviceId,
                                    'old-amount' => $model->arServAmount[$serviceId]
                                ])?>
                            </div>
                            <div class="form-group col-md-6 col-sm-6 col-xs-12">
                                <label class="control-label">Шаблон услуги</label>
                                <?=Html::dropDownList('BillForm[arServTpl]['.$serviceId.']',$model->arServTpl[$serviceId],BillTemplate::getBillTemplateMap(),[
                                    'id' => 'sel'.$serviceId,
                                    'class' => 'form-control tpl',
                                    'data-serv-id' => $serviceId
                                ])?>
                            </div>
                            <div class="form-group col-md-12 col-sm-12 col-xs-12">
                                <label class="control-label">Предмет счета</label>
                                <?=Html::textarea('BillForm[arServTitle]['.$serviceId.']',$model->arServTitle[$serviceId],[
                                    'class' => 'form-control serv-title',
                                    'data-serv-id' => $serviceId
                                ])?>
                            </div>
                            <div class="form-group col-md-6 col-sm-6 col-xs-12">
                                <label class="control-label">Описание</label>
                                <?=Html::textarea('BillForm[arServDesc]['.$serviceId.']',$model->arServDesc[$serviceId],[
                                    'class' => 'form-control serv-desc',
                                    'data-serv-id' => $serviceId
                                ])?>
                            </div>
                            <div class="form-group col-md-6 col-sm-6 col-xs-12">
                                <label class="control-label">Договор оферты</label>
                                <?=Html::textarea('BillForm[arServContract]['.$serviceId.']',$model->arServContract[$serviceId],[
                                    'class' => 'form-control serv-contract',
                                    'data-serv-id' => $serviceId
                                ])?>
                            </div>
                            <div class="clearfix"></div>
                        </li>
                    <?php endforeach;?>
                </ul>
            </div>
        </div>
    </div>

    <?= $form->field($model,'fAmount')->textInput(['maxlength' => true])?>

    <?= $form->field($model, 'sDescription')->textarea(['rows' => 6]) ?>

    <?= $form->field($model,'sOfferContract')->textInput();?>
    
    <div class="form-group">
        <div class = "col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
            <?= $form->errorSummary($model); ?>
        </div>
        <div class = "col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
            <?= Html::submitButton(Yii::t('app/documents', 'Save'), ['class' => 'btn btn-success']) ?>
        </div>
    </div>

    <?php ActiveForm::end();?>
</div>