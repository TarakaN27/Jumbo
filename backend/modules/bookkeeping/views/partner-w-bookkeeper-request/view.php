<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\PartnerWBookkeeperRequest */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/book', 'Partner Wbookkeeper Requests'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
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
                        <?= DetailView::widget([
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
                        ]) ?>
                </div>
            </div>
        </div>
    </div>
