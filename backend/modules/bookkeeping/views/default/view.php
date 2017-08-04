<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\Payments */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/book', 'Payments'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class = "row">
    <div class = "col-md-12 col-sm-12 col-xs-12">
        <div class = "x_panel">
            <div class = "x_title">
                <h2><?= Html::encode($this->title) ?></h2>
                <section class="pull-right">
                    <?= Html::a(Yii::t('app/book', 'To list'), ['index'], ['class' => 'btn btn-warning']) ?>
                    <?php if(Yii::$app->user->can('adminRights') || Yii::$app->user->can('only_bookkeeper')): ?>
                    <?php //Html::a(Yii::t('app/book', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                    <?= Html::a(Yii::t('app/book', 'Delete'), ['delete', 'id' => $model->id], [
                        'class' => 'btn btn-danger',
                        'data' => [
                        'confirm' => Yii::t('app/book', 'Are you sure you want to delete this item?'),
                        'method' => 'post',
                        ],
                    ]) ?>
                    <?php elseif(Yii::$app->user->can('only_manager') && false):?>
                    <?= Html::a(Yii::t('app/book', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                    <?php endif;?>
                </section>
                <div class = "clearfix"></div>
            </div>
            <div class = "x_content">
                <?= DetailView::widget([
                    'model' => $model,
                    'attributes' => [
                        'cuser.infoWithSite',
                        'cuser.manager.fio',
                        [
                            'attribute' => 'payRequest.manager.fio',
                            'label' => Yii::t('app/book','Payment request owner')
                        ],
                        [
                            'attribute' => 'pay_date',
                            'value' => Yii::$app->formatter->asDate($model->pay_date)
                        ],
                        'pay_summ:decimal',
                        [
                            'attribute' => 'currency_id',
                            'value' => is_object($obCurrency = $model->currency) ? '('.$obCurrency->code.') '.$obCurrency->name : 'N/A'
                        ],
                        [
                            'attribute' => 'service_id',
                            'value' => is_object($obService = $model->service) ? $obService->name : 'N/A'
                        ],
                        [
                            'attribute' => 'legal_id',
                            'value' => is_object($obLegal = $model->legal) ? $obLegal->name : 'N/A'
                        ],
                        'payment_order',
                        'description:ntext',
                        [
                            'attribute' => 'created_at',
                            'value' => is_null($model->created_at) ? NULL : $model->getFormatedCreatedAt()
                        ],
                        [
                            'attribute' => 'updated_at',
                            'value' => is_null($model->updated_at) ? NULL : $model->getFormatedUpdatedAt()
                        ],
                    ],
                ]) ?>
            </div>
            <?php if(Yii::$app->user->can('adminRights')): ?>
            <div class = "x_content">
                <h3><?=Yii::t('app/book','Payment detail')?></h3>
                <?php
                    $obDetail = $model->calculate;
                    if(is_object($obDetail)):
                ?>
                    <?=DetailView::widget([
                        'model' => $obDetail,
                        'attributes' => [
                            'profit:decimal',
                            'production:decimal',
                            'tax:decimal',
                            [
                                'attribute' => 'pay_cond_id',
                                'value' => is_object($obCond = $obDetail->payCond) ? $obCond->name : 'N/A'
                            ],
                            'cnd_tax:decimal',
                            'cnd_sale:decimal',
                            'cnd_commission:decimal',
                            'cnd_corr_factor:decimal',
                            [
                                'attribute' => 'created_at',
                                'value' => Yii::$app->formatter->asDatetime($obDetail->created_at)
                            ],
                            [
                                'attribute' => 'updated_at',
                                'value' => Yii::$app->formatter->asDatetime($obDetail->updated_at)
                            ]
                        ]
                    ])?>
                <?php endif;?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
