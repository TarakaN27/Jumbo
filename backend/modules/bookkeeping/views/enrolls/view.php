<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\Enrolls */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/book', 'Enrolls'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
    <div class = "row">
        <div class = "col-md-12 col-sm-12 col-xs-12">
            <div class = "x_panel">
                <div class = "x_title">
                    <h2><?= Html::encode($this->title) ?></h2>
                    <section class="pull-right">
                        <?=  Html::a(Yii::t('app/book', 'To list'), ['index'], ['class' => 'btn btn-warning']) ?>
                        <?= Html::a(Yii::t('app/book', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                        <?= Html::a(Yii::t('app/book', 'Delete'), ['delete', 'id' => $model->id], [
                        'class' => 'btn btn-danger',
                        'data' => [
                        'confirm' => Yii::t('app/book', 'Are you sure you want to delete this item?'),
                        'method' => 'post',
                        ],
                        ]) ?>
                    </section>
                    <div class = "clearfix"></div>
                </div>
                <div class = "x_content">
                        <?= DetailView::widget([
                            'model' => $model,
                            'attributes' => [
                                'id',
                                'amount:decimal',
                                'repay:decimal',
                                'enroll:decimal',
                                'enr_req_id',
                                [
                                    'attribute' => 'enroll_unit_id',
                                    'value' => $model->unitEnroll?$model->unitEnroll->name:NULL,
                                ],
                                [
                                    'attribute' => 'cuser_id',
                                    'value' => is_object($obCuser = $model->cuser) ? $obCuser->getInfo() : NULL
                                ],
                                [
                                    'attribute' => 'buser_id',
                                    'value' => is_object($obBuser = $model->buser) ? $obBuser->getFio() : NULL
                                ],
                                [ 'attribute'=>'description',
                                    'label' => Yii::t('app/book', 'Enrolls Description'),
                                ],
                                [ 'attribute'=>'enrReq.payment.description',
                                    'label' => Yii::t('app/book', 'Payment Description'),
                                ],
                                'created_at:datetime',
                                'updated_at:datetime',
                            ],
                        ]) ?>
                </div>
            </div>
        </div>
    </div>
