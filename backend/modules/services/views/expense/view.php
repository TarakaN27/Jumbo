<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\ExpenseCategories */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/services', 'Expense Categories'), 'url' => ['index']];
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
                                    <?php if(Yii::$app->user->can('superRights')):?>
                                    <?= Html::a(Yii::t('app/services','Create expense Categories'),['create'],['class'=>'btn btn-primary']);?>
                                    <?php endif;?>
                                    <?= Html::a(Yii::t('app/services', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                                    <?php if(Yii::$app->user->can('superRights')):?>
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
            'description',
            [
                'attribute' => 'parent_id',
                'value' => is_object($obParent = $model->parent) ? $obParent->name : NULL

            ],
            [
                'attribute' => 'status',
                'value' => $model->getStatusStr()
            ],
            [
                'attribute' => 'create_at',
                'value' => $model->getFormatedCreatedAt()
            ],
            [
                'attribute' => 'update_at',
                'value' => $model->getFormatedUpdatedAt()
            ],
        ],
    ]) ?>
                               </div>
                            </div>
                        </div>
</div>
