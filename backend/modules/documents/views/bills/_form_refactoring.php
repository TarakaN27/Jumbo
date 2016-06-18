<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 13.6.16
 * Time: 17.47
 */
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
?>
<?php Modal::begin([
    'id' => 'activity-modal',
    'header' => '<h2>'.Yii::t('app/documents','Add services').'</h2>',
    'size' => Modal::SIZE_DEFAULT,
    'footer' => Html::button(Yii::t('app/documents','Save'),['class' => 'btn btn-info btn-sm'])
]);?>
<?php foreach (Services::getServicesMap() as $servID => $servName):?>
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
            'class' => 'form-horizontal form-label-left'
        ],
        'fieldConfig' => [
            'template' => '<div class="form-group">{label}<div class="col-md-6 col-sm-6 col-xs-12">{input}</div><ul class="parsley-errors-list" >{error}</ul></div>',
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
    
    <?= $form->field($model,'iLegalPerson')->dropDownList(LegalPerson::getLegalPersonMap(),[
        'placeholder' => Yii::t('app/documents','Choose legal person')
    ]);?>

    <?= $form->field($model, 'iDocxTpl')->dropDownList(BillDocxTemplate::getBillDocxMap()) ; ?>

    <?= $form->field($model,'fAmount')->textInput(['maxlength' => true])?>

    <?= $form->field($model,'bUseTax')->dropDownList(Bills::getYesNo())?>

    <?= $form->field($model,'bTaxRate')->textInput(['maxlength' => true])?>

    <?= $form->field($model, 'sBayTarget')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12"><?=Yii::t('app/book','Services');?></label>
        <div class="col-md-6 col-sm-6 col-xs-12" >
            <div class="text-center">
                <?=\yii\helpers\Html::button('<i class="fa fa-plus-square"></i>',['class' => 'btn btn-info btn-xs','id' => 'addServId'])?>
            </div>
            <div class="well">
                <div class="servPreloader loader mrg-auto hide"></div>
                <ul class="ul-sortable" id="servicesBlock">
                    <?php foreach ($arServices as $key => $service):?>
                        <li class="block-sortable" >
                            <h4><?=ArrayHelper::getValue($service,'service.name');?>
                                <a href="#nogo" data-toggle="tooltip" data-placement="top" data-original-title="Удалить услугу" class="pull-right red  marg-l-10">
                                    <i class="fa fa-minus" data-serv="<?=$service->service_id;?>"></i>
                                </a>
                                <a href="#nogo" data-id="<?=$service->service_id;?>" data-choose="0" data-toggle="tooltip" data-placement="top" data-original-title="Выбрать описание и договор" class="pull-right red chooseService">
                                    <i class="fa fa-check"></i>
                                </a>
                            </h4>
                            <?=Html::hiddenInput('BillForm[arServices][]',$service->service_id,['class' => 'arServClass'])?>
                            <?=Html::hiddenInput('BillForm[arServOrder]['.$service->service_id.']',$key,['class' => 'service-order'])?>

                            <div class="form-group col-md-6 col-sm-6 col-xs-12">
                                <label class="control-label">Сумма</label>
                                <?=Html::textInput('"BillForm[arServAmount]['.$service->service_id.']"',$service->amount,[
                                    'class' => 'form-control serv-amount',
                                    'data-serv-id' => $service->service_id,
                                    'old-amount' => $service->amount
                                ])?>
                            </div>
                            <div class="form-group col-md-6 col-sm-6 col-xs-12">
                                <label class="control-label">Шаблон услуги</label>
                                <?=Html::dropDownList('BillForm[arServTpl][1]',$service->serv_tpl_id,BillTemplate::getBillTemplateMap(),[
                                    'id' => 'sel'.$service->service_id,
                                    'class' => 'form-control tpl',
                                    'data-serv-id' => $service->service_id
                                ])?>
                            </div>
                            <div class="form-group col-md-12 col-sm-12 col-xs-12">
                                <label class="control-label">Предмет счета</label>
                                <?=Html::textarea('BillForm[arServTitle]['.$service->service_id.']',$service->serv_title,[
                                    'class' => 'form-control serv-title',
                                    'data-serv-id' => $service->service_id
                                ])?>
                            </div>
                            <div class="form-group col-md-6 col-sm-6 col-xs-12">
                                <label class="control-label">Описание</label>
                                <?=Html::textarea('BillForm[arServDesc]['.$service->service_id.']',$service->description,[
                                    'class' => 'form-control serv-desc',
                                    'data-serv-id' => $service->service_id
                                ])?>
                            </div>
                            <div class="form-group col-md-6 col-sm-6 col-xs-12">
                                <label class="control-label">Договор оферты</label>
                                <?=Html::textarea('BillForm[arServContract]['.$service->service_id.']',$service->offer,[
                                    'class' => 'form-control serv-contract',
                                    'data-serv-id' => $service->service_id
                                ])?>
                            </div>
                            <div class="clearfix"></div>
                        </li>
                    <?php endforeach;?>
                </ul>
            </div>
        </div>
    </div>

    <?= $form->field($model, 'sDescription')->textarea(['rows' => 6]) ?>

    <?= $form->field($model,'sOfferContract')->textInput();?>
    
    <div class="form-group">
        <div class = "col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
            <?= $form->errorSummary($model); ?>
        </div>
        <div class = "col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
            <?= Html::submitButton(Yii::t('app/documents', 'Create'), ['class' => 'btn btn-success']) ?>
        </div>
    </div>

    <?php ActiveForm::end();?>
</div>