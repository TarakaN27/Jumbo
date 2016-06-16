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
CustomViewHelper::registerJsFileWithDependency('@web/js/vendor/bower/html.sortable/dist/html.sortable.min.js',$this,[],'html-sortable');
CustomViewHelper::registerJsFileWithDependency('@web/js/parts/bill_refactoring.js',$this,['html-sortable']);
$this->registerJs('
var
    arServTplOptions = '.Json::encode(BillTemplate::getBillTemplateMap()).',
    arServMap = '.Json::encode(Services::getServicesMap()).';
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
                'url' => \yii\helpers\Url::to(['/ajax-select/get-expense-user']),
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

    <?= $form->field($model, 'sDescription')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'sBayTarget')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12"><?=Yii::t('app/book','Services');?></label>
        <div class="col-md-6 col-sm-6 col-xs-12" >
            <div class="text-center">
                <?=\yii\helpers\Html::button('<i class="fa fa-plus-square"></i>',['class' => 'btn btn-info btn-xs','id' => 'addServId'])?>
            </div>
            <div class="well">
                <ul class="ul-sortable" id="servicesBlock">
                </ul>
            </div>
        </div>
    </div>
    
    <?php ActiveForm::end();?>
</div>