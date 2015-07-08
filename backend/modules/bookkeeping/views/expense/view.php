<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\Expense */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/book', 'Expenses'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class = "clearfix"></div>
<div class = "row">

<div class = "col-md-12 col-sm-12 col-xs-12">
                            <div class = "x_panel">
                                <div class = "x_title">
                                    <h2><?= Html::encode($this->title) ?></h2>
                                    <section class="pull-right">
                                    <?= Html::a(Yii::t('app/book', 'To list'), ['index'], ['class' => 'btn btn-warning']) ?>
                                    <?= Html::a(Yii::t('app/book','Create expense'),['create'],['class'=>'btn btn-primary']);?>
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
            [
                'attribute' => 'pay_date',
                'value' => $model->getFormatedPayDate()
            ],
            'pay_summ',
            [
                'attribute' => 'currency_id',
                'value' => is_object($curr = $model->currency) ? '('.$curr->code.') '.$curr->name : 'N/A'
            ],
            [
                'attribute' => 'legal_id',
                'value' => is_object($legal = $model->legal) ? $legal->name : 'N/A'
            ],
            [
                'attribute' => 'cuser_id',
                'value' => is_object($cuser = $model->cuser) ? $cuser->username : 'N/A'
            ],
            [
                'attribute' => 'cat_id',
                'value' => is_object($cat = $model->cat) ? $cat->name : 'N/A'
            ],
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
                            </div>
                        </div>
</div>
