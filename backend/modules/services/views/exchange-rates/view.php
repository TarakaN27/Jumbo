<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\ExchangeRates */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/services', 'Exchange Rates'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class = "clearfix"></div>
<div class = "row">

<div class = "col-md-12 col-sm-12 col-xs-12">
                            <div class = "x_panel">
                                <div class = "x_title">
                                    <h2><?= Html::encode($this->title) ?></h2>
                                    <section class="pull-right">
                                         <?= Html::a(Yii::t('app/services', 'To list'), ['index'], ['class' => 'btn btn-warning']) ?>
                                    <?php if(Yii::$app->user->can('adminRights') ||Yii::$app->user->can('only_bookkeeper')):?>
                                        <?= Html::a(Yii::t('app/services', 'Update_rates_now'), ['update-rates', 'id' => $model->id], ['class' => 'btn btn-success']) ?>
                                        <?= Html::a(Yii::t('app/services','Create Exchange Rates'),['create'],['class'=>'btn btn-primary']);?>
                                        <?= Html::a(Yii::t('app/services', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                                        <?= Html::a(Yii::t('app/services', 'Delete'), ['delete', 'id' => $model->id], [
                                            'class' => 'btn btn-danger',
                                            'data' => [
                                                'confirm' => Yii::t('app/services', 'Are you sure you want to delete this item?'),
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
                                            'name',
                                            'show_at_widget:boolean',
                                            'code',
                                            'nbrb',
                                            'cbr',
                                            [
                                                'attribute' => 'nbrb_rate',
                                                'format'=>['decimal',4]
                                            ],
                                            [
                                                'attribute' => 'cbr_rate',
                                                'format'=>['decimal',4]
                                            ],
                                            [
                                                'attribute' => 'use_rur_for_byr',
                                                'value' => $model->getYesNoStr($model->use_rur_for_byr)
                                            ],
                                            [
                                              'attribute' => 'use_base',
                                              'value' => $model->getYesNoStr($model->use_base)
                                            ],
                                            [
                                                'attribute' => 'base_id',
                                                'value' => is_object($obBase = $model->base) ? $obBase->code : NULL
                                            ],
                                            [
                                                'attribute' => 'factor',
                                                'format'=>['decimal',4]
                                            ],
                                            [
                                                'attribute' => 'use_exchanger',
                                                'value' => $model->getYesNoStr($model->use_exchanger)
                                            ],
                                            'bank_id',
                                            [
                                                'attribute' => 'is_default',
                                                'value' => $model->getIsDefaultStr()
                                            ],
                                            [
                                                'attribute' => 'doc_n2w_type',
                                                'value' => $model->getN2MStr()
                                            ],
                                            [
                                                'attribute' => 'need_upd',
                                                'value' => $model->getNeedUpdateStr()
                                            ],
                                            [
                                                'attribute' => 'created_at',
                                                'value' => $model->getFormatedCreatedAt()
                                            ],
                                            [
                                                'attribute' => 'updated_at',
                                                'value' => $model->getFormatedUpdatedAt()
                                            ],
                                        ],
                                    ]) ?>
                                 </div>
                            </div>
                        </div>
</div>
