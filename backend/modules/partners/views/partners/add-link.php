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
use yii\bootstrap\Modal;
$this->registerCssFile('@web/css/select/select2.min.css');
$this->registerJsFile('@web/js/select/select2.full.js',['depends' => ['yii\web\YiiAsset', 'yii\bootstrap\BootstrapAsset']]);
$this->registerJsFile('@web/js/datepicker/daterangepicker.js',['depends' => ['yii\web\YiiAsset', 'yii\bootstrap\BootstrapAsset']]);
$this->registerJsFile('@web/js/moment.min2.js',['depends' => ['yii\web\YiiAsset', 'yii\bootstrap\BootstrapAsset']]);
$this->registerJsFile('@web/js/parts/partner_add_link.js',['depends' => ['yii\web\YiiAsset', 'yii\bootstrap\BootstrapAsset']]);
$this->registerJs("
var
    pid = ".$pid.",
    ajaxSelectGetCmpUrl = '".\yii\helpers\Url::to(['/ajax-select/get-cmp'])."',
    ajaxMultiLinkFormUrl = '".\yii\helpers\Url::to(['get-multi-link-form'])."',
    select2Data = ".\yii\helpers\Json::encode($select2Data).";
",\yii\web\View::POS_HEAD);
?>
<?php \common\components\customComponents\Modal\CustomModal::begin([
    'id' => 'activity-modal',
    'header' => '<h2>'.Yii::t('app/crm','Multiple link ').'</h2>',
    'size' => Modal::SIZE_LARGE,
]);?>

<?php \common\components\customComponents\Modal\CustomModal::end(); ?>
<div class = "row">
    <div class = "col-md-12 col-sm-12 col-xs-12">
        <div class = "x_panel">
            <div class = "x_title">
                <h2><?php echo Html::encode($this->title)?></h2>
                <section class="pull-right">
                    <?= Html::a(Yii::t('app/users', 'Multiple link'), NULL, ['class' => 'btn btn-info','id' => 'multipleLinkAdd']) ?>
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
                            //'limit' => 4, // the maximum times, an element can be cloned (default 999)
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
                                                    $arServMap,
                                                    [
                                                        'prompt' => Yii::t('app/users','Choose service'),
                                                        'class' => 'form-control service'
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
