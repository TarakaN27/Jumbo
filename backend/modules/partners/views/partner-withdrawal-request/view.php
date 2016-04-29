<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\PartnerWithdrawalRequest */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/users', 'Partner Withdrawal Requests'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
    <div class = "row">
        <div class = "col-md-12 col-sm-12 col-xs-12">
            <div class = "x_panel">
                <div class = "x_title">
                    <h2><?= Html::encode($this->title) ?></h2>
                    <section class="pull-right">
                        <?=  Html::a(Yii::t('app/users', 'To list'), ['index'], ['class' => 'btn btn-warning']) ?>
                        <?= Html::a(Yii::t('app/users', 'Create Partner Withdrawal Request'), ['create'], ['class' => 'btn btn-success']) ?>
                        <?php if($model->status == \common\models\PartnerWithdrawalRequest::STATUS_NEW):?>
                            <?= Html::a(Yii::t('app/users', 'Delete'), ['delete', 'id' => $model->id], [
                                    'class' => 'btn btn-danger',
                                    'data' => [
                                    'confirm' => Yii::t('app/users', 'Are you sure you want to delete this item?'),
                                    'method' => 'post',
                                ],
                            ]) ?>
                        <?php endif;?>
                    </section>
                    <div class = "clearfix"></div>
                </div>
                <div class = "x_content">
                        <?= DetailView::widget([
                            'model' => $model,
                            'attributes' => [
                                'id',
                                [
                                    'attribute' => 'partner_id',
                                    'value' =>  is_object($obP = $model->partner) ? $obP->infoWithSite : NULL   /*'partner.infoWithSite'*/
                                ],
                                [
                                    'attribute' => 'type',
                                    'value' => $model->getTypeStr()
                                ],
                                'amount:decimal',
                                [
                                    'attribute' => 'currency_id',
                                    'value' => is_object($oCurr = $model->currency) ? $oCurr->code : NULL
                                ],
                                [
                                    'attribute' => 'manager_id',
                                    'value' => is_object($obMan = $model->manager) ? $obMan->getFio() : NULL
                                ],
                                [
                                    'attribute' => 'created_by',
                                    'value' => is_object($obCreated = $model->createdBy) ? $obCreated->getFio() : NULL
                                ],
                                'date:date',
                                'description:text',
                                [
                                    'attribute' => 'status',
                                    'value' => $model->getStatusStr()
                                ],
                                'created_at:datetime',
                                'updated_at:datetime',
                            ],
                        ]) ?>
                </div>
            </div>
        </div>
    </div>
