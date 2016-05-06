<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 6.5.16
 * Time: 11.51
 */
use yii\helpers\Html;
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
                    <div class="col-md-6 col-sm-6 col-xs-12">
                        <?php $form = \yii\bootstrap\ActiveForm::begin([
                            'options' => [
                                'enctype' => 'multipart/form-data'
                            ],
                        ]);
                        echo Html::activeHiddenInput($model,'status',[]);
                        ?>

                        <?= \nemmo\attachments\components\AttachmentsInput::widget([
                            'id' => 'file-input', // Optional
                            'model' => $model,
                            'options' => [ // Options of the Kartik's FileInput widget
                                'multiple' => true, // If you want to allow multiple upload, default to false
                            ],
                            'pluginOptions' => [ // Plugin options of the Kartik's FileInput widget
                                'maxFileCount' => 10 // Client max files
                            ]
                        ]) ?>
                    </div>

                </div>
                <div class="row">
                    <div class="form-group">
                        <div class = "col-md-12 col-sm-12 col-xs-12">
                            <?= Html::submitButton(Yii::t('app/users', 'Save'), ['class' => 'btn btn-success']) ?>
                        </div>
                    </div>
                </div>
                <?php \yii\bootstrap\ActiveForm::end();?>
            </div>
        </div>
    </div>
</div>
