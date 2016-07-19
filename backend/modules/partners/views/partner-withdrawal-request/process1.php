<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 27.4.16
 * Time: 16.39
 */
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use wbraganca\dynamicform\DynamicFormWidget;
use common\models\LegalPerson;
$this->registerJsFile('@web/js/parts/pw_process_1_form.js',[
    'depends' => [
        'yii\web\JqueryAsset',
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapPluginAsset',
    ]
]);
$this->title = Yii::t('app/users','Partner withdrawal request');
?>
<div class = "row">
    <div class = "col-md-12 col-sm-12 col-xs-12">
        <div class = "x_panel">
            <div class = "x_title">
                <h2><?= Html::encode($this->title) ?></h2>
                <section class="pull-right">
                </section>
                <div class = "clearfix"></div>
            </div>
            <div class = "x_content">
                <div class="row">
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <?php echo \yii\widgets\DetailView::widget([
                                'model' => $model,
                                'attributes' => [
                                    'id',
                                    [
                                        'attribute' => 'created_by',
                                        'value' => is_object($obCrBy = $model->createdBy) ? $obCrBy->getFio() : NULL
                                    ],
                                    'amount:decimal',
                                    [
                                        'attribute' => 'currency_id',
                                        'value' => is_object($obCurr = $model->currency) ? $obCurr->code : NULL
                                    ],
                                    'date:date',
                                    [
                                        'attribute' => 'type',
                                        'value' => $model->getTypeStr()
                                    ],
                                    [
                                        'attribute' => 'status',
                                        'value' => $model->getStatusStr()
                                    ],
                                    'created_at:datetime',
                                    'description:text'
                                ]
                            ])?>
                        </div>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <p><?=Yii::t('app/users','Available amount')?>:<span id="avAmount" data-amount="<?=$model->amount;?>"></span></p>
                            <p><?=Yii::t('app/users','Bookkeeper for partner withdrawal')?>:
                                <span class="<?php if(!is_object($obBookkeeper)):?>colorDanger<?php else:?>colorSuccess  <?php endif;?>">
                                    <?=is_object($obBookkeeper) ? $obBookkeeper->getFio() : Yii::t('app/users','Warning! You must set bookkeeper for partner withdrawal!');?>
                                </span>
                            </p>
                        </div>
                </div>
                <div class="row">
                    <div class="col-md-12 col-sm-12 col-xs-12">
                        <?php $form=ActiveForm::begin([
                            'id' => 'dynamic-form'
                        ]);?>
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
                                                <h3 class="panel-title pull-left"><?=Yii::t('app/users','Partner withdrawal params')?></h3>
                                                <div class="pull-right">
                                                    <button type="button" class="add-item btn btn-success btn-xs"><i class="glyphicon glyphicon-plus"></i></button>
                                                    <button type="button" class="remove-item btn btn-danger btn-xs"><i class="glyphicon glyphicon-minus"></i></button>
                                                </div>
                                                <div class="clearfix"></div>
                                            </div>
                                            <div class="panel-body">
                                                <div class="row">
                                                    <div class="col-sm-4 wm-select-2-style">
                                                        <?= $form->field($model, "[{$i}]amount")->textInput([
                                                            'class' => 'amounts form-control'
                                                        ]);?>
                                                    </div>
                                                    
                                                    <div class="col-sm-4">
                                                        <?= $form->field($model, "[{$i}]contractor")->dropDownList(
                                                            $arContractor,
                                                            [
                                                                'prompt' => Yii::t('app/users','Choose contractor')
                                                            ]
                                                        ) ?>
                                                    </div>
                                                </div><!-- .row -->
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php DynamicFormWidget::end(); ?>
                            </div>
                        </div>
                        <?php if($obBookkeeper):?>
                            <div class="form-group">
                                <div class = "col-md-6 col-sm-6 col-xs-12">
                                    <?= Html::submitButton(Yii::t('app/users', 'Save'), ['class' => 'btn btn-success']) ?>
                                </div>
                            </div>
                        <?php endif;?>
                        <?php ActiveForm::end();?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
