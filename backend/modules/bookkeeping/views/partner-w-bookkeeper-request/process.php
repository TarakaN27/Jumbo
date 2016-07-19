<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 6.5.16
 * Time: 11.51
 */
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\LegalPerson;
$this->title = Yii::t('app/book','Process partner withdrawal request');
?>
<div class = "row">
    <div class = "col-md-12 col-sm-12 col-xs-12">
        <div class = "x_panel">
            <div class = "x_title">
                <h2><?= Html::encode($this->title) ?></h2>
                <section class="pull-right">
                    <?=  Html::a(Yii::t('app/book', 'To list'), ['index'], ['class' => 'btn btn-warning']) ?>
                </section>
                <div class = "clearfix"></div>
            </div>
            <div class = "x_content">

                <div class="row">
                    <div class="col-md-6 col-sm-6 col-xs-12">
                        <?=\yii\widgets\DetailView::widget([
                            'model' => $model,
                            'attributes' => [
                                'id',
                                [
                                    'attribute' => 'buser_id',
                                    'value' => is_object($obBUser = $model->buser) ? $obBUser->getFio() : NULL
                                ],
                                [
                                    'attribute' => 'partner_id',
                                    'value' => is_object($obPartner = $model->partner) ? $obPartner->getInfoWithSite() : NULL
                                ],
                                [
                                    'attribute' => 'contractor_id',
                                    'value' => is_object($obCntr = $model->contractor) ? $obCntr->getInfoWithSite() : NULL
                                ],
                                'amount:decimal',
                                [
                                    'attribute' => 'currency_id',
                                    'value' => is_object($obCurr = $model->currency) ? $obCurr->code : NULL
                                ],
                                'factual_amount_in_base_currency:decimal',
                                [
                                    'attribute' => 'legal_id',
                                    'value' => is_object($obLegal = $model->legal) ? $obLegal->name : NULL
                                ],
                                'request_id',
                                'created_by:datetime',
                                [
                                    'attribute' => 'created_by',
                                    'value' => is_object($obCrt = $model->createdBy) ? $obCrt->getFio() : NULL
                                ],
                                [
                                    'attribute' => 'status',
                                    'value' => $model->getStatusStr()
                                ],
                                'created_at:datetime',
                                'description:text'
                            ],
                        ])?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <?php $form=ActiveForm::begin([
            'id' => 'dynamic-form'
        ]);?>
        <div class="panel panel-default">
            <div class="panel-body">
                <div class="container-items"><!-- widgetContainer -->
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
                                        <?= $form->field($model, "factual_amount_in_base_currency")->textInput([
                                            'class' => 'amounts form-control'
                                        ]);?>
                                    </div>
                                    <div class="col-sm-4">
                                        <?= $form->field($model, "legal_id")->dropDownList(
                                            LegalPerson::getLegalPersonMap(),
                                            [
                                                'prompt' => Yii::t('app/users','Choose legal person')
                                            ]
                                        ) ?>
                                    </div>
                                    <div class="col-sm-4">
                                        <?= $form->field($model, "contractor")->dropDownList(
                                            $arContractor,
                                            [
                                                'prompt' => Yii::t('app/users','Choose contractor')
                                            ]
                                        ) ?>
                                    </div>
                                </div><!-- .row -->
                            </div>
                        </div>
                </div>
                <div class="form-group">
                    <div class = "col-md-6 col-sm-6 col-xs-12">
                        <?= Html::submitButton(Yii::t('app/users', 'Save'), ['class' => 'btn btn-success']) ?>
                    </div>
                </div>
            </div>
        </div>

        <?php ActiveForm::end();?>
    </div>
</div>
