<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 20.5.16
 * Time: 12.29
 */
use yii\web\JsExpression;
$this->registerJsFile('@web/js/vendor/bower/html.sortable/dist/html.sortable.min.js',[
    'depends' => [
        'yii\web\JqueryAsset',
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapPluginAsset',
    ]
],'html-sortable');
$this->registerJsFile('@web/js/parts/act_form_v2.js',[
    'depends' => [
        'yii\web\JqueryAsset',
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapPluginAsset',
        'html-sortable'
    ]
]);
?>
<div class="act-form-v2">
    <?php $form=\yii\bootstrap\ActiveForm::begin([
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

    <?=$form->field($model,'iLegalPerson')->dropDownList(\common\models\LegalPerson::getLegalPersonMap(),[
        'prompt' => Yii::t('app/book','Choose legal person')
    ])?>

    <?=$form->field($model,'iActNumber')->textInput();?>

    <?=$form->field($model,'actDate')->widget(\yii\jui\DatePicker::className(),[
        'language' => 'ru',
        'dateFormat' => 'dd-MM-yyyy',
    ]);?>








    <?php \yii\bootstrap\ActiveForm::end();?>
</div>


