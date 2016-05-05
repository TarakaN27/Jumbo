<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 29.4.16
 * Time: 15.41
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
$this->registerJsFile('@web/js/parts/pw_process_3_form.js',[
    'depends' => [
        'yii\web\JqueryAsset',
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapPluginAsset',
    ]
]);
$this->registerJs('
    var
        FIND_CONDITION_URL = "'.\yii\helpers\Url::to(['/ajax-select/get-condition']).'";
',\yii\web\View::POS_HEAD);

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
                <div id="preloader">
                    <div class="loader mrg-auto"></div>
                </div>
                <div class="row pMainContent hide">
                    <div class="col-md-6 col-sm-6 col-xs-12">
                        <?=Html::hiddenInput('pay_date',$model->date,['id' => 'pay_date']);?>
                        <?=Html::hiddenInput('currency_id',$model->currency_id,['id' => 'currency_id']);?>

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
                    </div>
                </div>
                <div class="row pMainContent hide">
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
                                    'model' => $arModels[0],
                                    'formId' => 'dynamic-form',
                                    'formFields' => [
                                        'cuser_id',
                                        'service_id',
                                        'connect',
                                    ],
                                ]); ?>

                                <div class="container-items"><!-- widgetContainer -->
                                    <?php foreach ($arModels as $i => $obModel): ?>
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
                                                        <?= $form->field($obModel, "[{$i}]amount")->textInput([
                                                            'class' => 'amounts form-control change-event'
                                                        ]);?>
                                                    </div>
                                                    <div class="col-sm-4 wm-select-2-style">
                                                        <?=$form->field($obModel,"[{$i}]contractorID")->dropDownList(
                                                            $arContractor,[
                                                                'class' => 'form-control change-event'
                                                            ]
                                                        )?>
                                                    </div>
                                                    <div class="col-sm-4 wm-select-2-style">
                                                        <?=$form->field($obModel,"[{$i}]serviceID")->dropDownList(
                                                            \common\models\Services::getServicesMap(),[
                                                                'prompt' => Yii::t('app/users','Choose service'),
                                                                 'class' => 'form-control change-event'
                                                            ]
                                                        )?>
                                                    </div>
                                                    <div class="col-sm-4">
                                                        <?= $form->field($obModel, "[{$i}]legalPersonID")->dropDownList(
                                                            LegalPerson::getLegalPersonMap(),
                                                            [
                                                                'prompt' => Yii::t('app/users','Choose legal person'),
                                                                'class' => 'form-control change-event'
                                                            ]
                                                        ) ?>
                                                    </div>
                                                    <div class="col-sm-4">
                                                        <?= $form->field($obModel, "[{$i}]conditionID")->dropDownList(
                                                            [],
                                                            [
                                                                'prompt' => Yii::t('app/users','Choose condition ID')
                                                            ]
                                                        ) ?>
                                                    </div>
                                                    <div class="col-sm-4">
                                                        <?= $form->field($obModel, "[{$i}]description")->textarea(); ?>
                                                    </div>
                                                </div><!-- .row -->
                                                <?php if(!empty($obModel->arCustomErrors)):?>
                                                    <div class="row">
                                                        <ul>
                                                            <?php foreach ($obModel->arCustomErrors as $strError):?>
                                                                <li>
                                                                    <?=Html::encode($strError);?>
                                                                </li>
                                                            <?php endforeach;?>
                                                        </ul>
                                                    </div>
                                                <?php endif;?>
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
    </div>
</div>
