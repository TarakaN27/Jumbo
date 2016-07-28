<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\PaymentCondition */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/book', 'Payment Conditions'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class = "row">
    <div class = "col-md-12 col-sm-12 col-xs-12">
        <div class = "x_panel">
            <div class = "x_title">
                <h2><?= Html::encode($this->title) ?></h2>
                <section class="pull-right">
                    <?= Html::a(Yii::t('app/book', 'To list'), ['index'], ['class' => 'btn btn-warning']) ?>
                    <?= Html::a(Yii::t('app/book','Create payment condition'),['create'],['class'=>'btn btn-primary']);?>
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
                        'name',
                        [
                            'attribute' => 'type',
                            'value' => $model->getTypeStr()
                        ],
                        'description:ntext',
                        [
                            'attribute' => 'service_id',
                            'value' => is_object($obServ = $model->service) ? $obServ->name : 'N/A'
                        ],
                        [
                            'attribute' => 'l_person_id',
                            'value' => is_object($obLP = $model->lPerson) ? $obLP->name : 'N/A'
                        ],
                        [
                            'attribute' => 'is_resident',
                            'value' => $model->getYesNoStr($model->is_resident)
                        ],
                        [
                            'attribute' => 'summ_from',
                            'format'=>['decimal',4]
                        ],
                        [
                            'attribute' => 'summ_to',
                            'format'=>['decimal',4]
                        ],
                        [
                            'attribute' => 'caurrency_id',
                            'value' => is_object($obCurr = $model->currency) ? $obCurr->code : 'N/A'
                        ],
                        [
                            'attribute' => 'corr_factor',
                            'format'=>['decimal',4]
                        ],
                        [
                            'attribute' => 'commission',
                            'format'=>['decimal',4]
                        ],
                        [
                            'attribute' => 'sale',
                            'format'=>['decimal',4]
                        ],
                        [
                            'attribute' => 'tax',
                            'format'=>['decimal',4]
                        ],
                        [
                            'attribute' => 'created_at',
                            'value' => Yii::$app->formatter->asDatetime($model->created_at)
                        ],
                        [
                            'attribute' => 'updated_at',
                            'value' => Yii::$app->formatter->asDatetime($model->updated_at)
                        ],
                    ],
                ]) ?>
            </div>
        </div>
    </div>
</div>
