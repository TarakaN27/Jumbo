<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\PromisedPayment */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/book', 'Promised Payments'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>


<div class = "row">

    <div class = "col-md-12 col-sm-12 col-xs-12">
        <div class = "x_panel">
            <div class = "x_title">
                <h2><?= Html::encode($this->title) ?></h2>
                <section class="pull-right">
                    <?= Html::a(Yii::t('app/book', 'To list'), ['index'], ['class' => 'btn btn-warning']) ?>
                </section>
                <div class = "clearfix"></div>
            </div>
            <div class = "x_content">
            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'id',
                    [
                        'attribute' => 'cuser_id',
                        'value' => is_object($obUser = $model->cuser) ? $obUser->getInfo() : 'N/A'
                    ],
                    [
                        'attribute' => 'buser_id_p',
                        'value' => is_object($obBuser = $model->buser) ? $obBuser->getFio() : 'N/A'
                    ],
                    [
                        'attribute' => 'amount',
                        'value' => $model->amount
                    ],
                    [
                        'attribute' => 'service_id',
                        'value' => is_object($obServ = $model->service) ? $obServ->name : 'N/A'
                    ],
                    [
                        'attribute' => 'paid_date',
                        'format' => 'html',
                        'value' => Yii::$app->formatter->asDatetime($model->paid_date)
                    ],
                    [
                        'attribute' => 'paid',
                        'value' => $model->getYesNoStr($model->paid)
                    ],
                    'description:text',
                    [
                        'attribute' => 'owner',
                        'value' => is_object($obOwner = $model->addedBy) ? $obOwner->getFio() : NULL
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

            <?=Html::tag('h3',Yii::t('app/book','Prepay history'))?>
                <?=\yii\grid\GridView::widget([
                    'dataProvider' => new \yii\data\ArrayDataProvider([
                        'allModels' => $obRepay
                    ]),
                    'columns' => [
                        'amount',
                        'payment_id',
                        'created_at:datetime'
                    ]
                ])?>

            </div>
        </div>
    </div>
</div>
