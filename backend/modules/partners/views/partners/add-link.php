<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 14.4.16
 * Time: 16.09
 */
use wbraganca\dynamicform\DynamicFormWidget;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\web\JsExpression;
$this->registerCssFile('@web/css/select/select2.min.css');
$this->registerJsFile('@web/js/select/select2.full.js',['depends' => ['yii\web\YiiAsset', 'yii\bootstrap\BootstrapAsset'],]);
$this->registerJsFile('@web/js/datepicker/daterangepicker.js',['depends' => ['yii\web\YiiAsset', 'yii\bootstrap\BootstrapAsset'],]);
$this->registerJsFile('@web/js/moment.min2.js',['depends' => ['yii\web\YiiAsset', 'yii\bootstrap\BootstrapAsset'],]);
$this->registerJs("
function swInitSelect2(item)
{
    item.select2({
            data : ".\yii\helpers\Json::encode($select2Data).",
            ajax: {
                url: '".\yii\helpers\Url::to(['/ajax-select/get-cmp'])."',
                dataType: 'json',
                delay: 250,
                data: function(params) { return {q:params.term}; },
                processResults: function (data, params) {return {results: data.results};},
                cache: true
            },
            escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
            minimumInputLength: 1,
            templateResult: function(cmp_id) { return cmp_id.text; }, // omitted for brevity, see the source of this page
            templateSelection: function (cmp_id) { return cmp_id.text; }, // omitted for brevity, see the source of this page
        });
}
function initDatePicker(item)
{
    item.daterangepicker({
        singleDatePicker: true,
        calender_style: \"picker_2\",
        locale :{
            format: 'DD.MM.YYYY',
        }
    });
}
",\yii\web\View::POS_END);

$this->registerJs("
swInitSelect2($('.wm-select2'));
initDatePicker($('.datePicker'));
$('.dynamicform_wrapper').on('afterInsert', function(e, item) {
    swInitSelect2($(item).find('.wm-select2'));
    initDatePicker($(item).find('.datePicker'));
});
",\yii\web\View::POS_READY);

?>
<div class = "row">
    <div class = "col-md-12 col-sm-12 col-xs-12">
        <div class = "x_panel">
            <div class = "x_title">
                <h2><?php echo Html::encode($this->title)?></h2>
                <section class="pull-right">
                    <?= Html::a(Yii::t('app/services', 'To list'), ['link-lead','pid' => $pid], ['class' => 'btn btn-warning']) ?>
                </section>
                <div class = "clearfix"></div>
            </div>
            <div class = "x_content">
                <?php $form = ActiveForm::begin(['id' => 'dynamic-form']); ?>
                <div class="panel panel-default">
                    <div class="panel-body">
                        <?php DynamicFormWidget::begin([
                            'widgetContainer' => 'dynamicform_wrapper', // required: only alphanumeric characters plus "_" [A-Za-z0-9_]
                            'widgetBody' => '.container-items', // required: css class selector
                            'widgetItem' => '.item', // required: css class
                            'limit' => 4, // the maximum times, an element can be cloned (default 999)
                            'min' => 1, // 0 or 1 (default 1)
                            'insertButton' => '.add-item', // css class
                            'deleteButton' => '.remove-item', // css class
                            'model' => $models[0],
                            'formId' => 'dynamic-form',
                            'formFields' => [
                                'cuser_id',
                                'service_id',
                                'connect',
                            ],
                        ]); ?>

                        <div class="container-items"><!-- widgetContainer -->
                            <?php foreach ($models as $i => $model): ?>
                                <div class="item panel panel-default"><!-- widgetBody -->
                                    <div class="panel-heading">
                                        <h3 class="panel-title pull-left"><?=Yii::t('app/users','Partner lead link')?></h3>
                                        <div class="pull-right">
                                            <button type="button" class="add-item btn btn-success btn-xs"><i class="glyphicon glyphicon-plus"></i></button>
                                            <button type="button" class="remove-item btn btn-danger btn-xs"><i class="glyphicon glyphicon-minus"></i></button>
                                        </div>
                                        <div class="clearfix"></div>
                                    </div>
                                    <div class="panel-body">
                                        <?php
                                        // necessary for update action.
                                        if (! $model->isNewRecord) {
                                            echo Html::activeHiddenInput($model, "[{$i}]id");
                                        }
                                        ?>
                                        <div class="row">
                                            <div class="col-sm-4 wm-select-2-style">
                                                <?= $form->field($model, "[{$i}]cuser_id")->dropDownList(
                                                    $select2Data,[
                                                    'class' => 'wm-select2 form-control'
                                                ]);
                                                ?>
                                            </div>
                                            <div class="col-sm-4">
                                                <?= $form->field($model, "[{$i}]service_id")->dropDownList(
                                                    \common\models\Services::getServicesMap(),
                                                    [
                                                        'prompt' => Yii::t('app/users','Choose service')
                                                    ]
                                                ) ?>
                                            </div>
                                            <div class="col-sm-4">
                                                <?= $form->field($model, "[{$i}]connect")->textInput([
                                                    'class' => 'datePicker form-control'
                                                ]) ?>
                                            </div>
                                        </div><!-- .row -->
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php DynamicFormWidget::end(); ?>
                    </div>
                </div>
                <div class="form-group">
                    <div class = "col-md-6 col-sm-6 col-xs-12">
                        <?= Html::submitButton(Yii::t('app/users', 'Save'), ['class' => 'btn btn-success']) ?>
                    </div>
                </div>
                <?php ActiveForm::end();?>
            </div>
        </div>
    </div>
</div>
