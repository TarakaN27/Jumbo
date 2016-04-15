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

$this->registerJs("

/*
$(\".dynamicform_wrapper\").on(\"afterInsert\", function(e, item) {
    console.log(item);
    console.log(e);
});
*/
",\yii\web\View::POS_READY);

?>
<div class = "row">
    <div class = "col-md-12 col-sm-12 col-xs-12">
        <div class = "x_panel">
            <div class = "x_title">
                <h2><?php echo Html::encode($this->title)?></h2>
                <section class="pull-right">

                </section>
                <div class = "clearfix"></div>
            </div>
            <div class = "x_content">
                <?php $form = ActiveForm::begin(['id' => 'dynamic-form']); ?>
                <div class="panel panel-default">
                    <div class="panel-heading"><h4><i class="glyphicon glyphicon-envelope"></i> <?=Yii::t('app/users','Partners lead links')?></h4></div>
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
                                'full_name',
                                'address_line1',
                                'address_line2',
                                'city',
                                'state',
                                'postal_code',
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
                                            <div class="col-sm-4">
                                                <?= $form->field($model, "[{$i}]cuser_id")->dropDownList(
                                                    $select2Data,[
                                                    'class' => '.wm-select2'
                                                ]);


                                                /*widget(\kartik\select2\Select2::className(),[
                                                    //'initValueText' => $sAssName, // set the initial display text
                                                    'data' => $select2Data,
                                                    'options' => [
                                                        'placeholder' => Yii::t('app/crm','Search for a users ...')
                                                    ],
                                                    'pluginOptions' => [
                                                        'allowClear' => true,
                                                        'minimumInputLength' => 2,
                                                        'ajax' => [
                                                            'url' => \yii\helpers\Url::to(['/ajax-select/get-cmp']),
                                                            'dataType' => 'json',
                                                            'data' => new JsExpression('function(params) { return {q:params.term}; }')
                                                        ],
                                                        'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                                                        'templateResult' => new JsExpression('function(cmp_id) { return cmp_id.text; }'),
                                                        'templateSelection' => new JsExpression('function (cmp_id) { return cmp_id.text; }'),
                                                    ],
                                                ]) */?>
                                            </div>
                                            <div class="col-sm-4">
                                                <?= $form->field($model, "[{$i}]service_id")->dropDownList(\common\models\Services::getServicesMap()) ?>
                                            </div>
                                            <div class="col-sm-4">
                                                <?= $form->field($model, "[{$i}]connect")->textInput(['maxlength' => true]) ?>
                                            </div>
                                        </div><!-- .row -->
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php DynamicFormWidget::end(); ?>
                    </div>
                </div>



                <?php ActiveForm::end();?>
            </div>
        </div>
    </div>
</div>
