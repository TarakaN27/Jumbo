<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\Partner */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/users', 'Partners'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
    <div class = "row">
        <div class = "col-md-12 col-sm-12 col-xs-12">
            <div class = "x_panel">
                <div class = "x_title">
                    <h2><?= Html::encode($model->getFio()) ?></h2>
                    <section class="pull-right">
                        <?=  Html::a(Yii::t('app/users', 'To list'), ['index'], ['class' => 'btn btn-warning']) ?>
                        <?= Html::a(Yii::t('app/users', 'Create Partner'), ['create'], ['class' => 'btn btn-success']) ?>
                        <?= Html::a(Yii::t('app/users', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                        <?= Html::a(Yii::t('app/users', 'Delete'), ['delete', 'id' => $model->id], [
                        'class' => 'btn btn-danger',
                        'data' => [
                        'confirm' => Yii::t('app/users', 'Are you sure you want to delete this item?'),
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
                                'fname',
                                'lname',
                                'mname',
                                'email:email',
                                'phone',
                                'description:ntext',
                                'post_address:ntext',
                                'ch_account:ntext',
                                [
                                    'attribute' => 'status',
                                    'value' => $model->getStatusStr()
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
