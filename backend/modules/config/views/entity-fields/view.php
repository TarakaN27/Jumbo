<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\EntityFields */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/config', 'Entity Fields'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
    <div class = "row">
        <div class = "col-md-12 col-sm-12 col-xs-12">
            <div class = "x_panel">
                <div class = "x_title">
                    <h2><?= Html::encode($this->title) ?></h2>
                    <section class="pull-right">
                        <?=  Html::a(Yii::t('app/config', 'To list'), ['index'], ['class' => 'btn btn-warning']) ?>
                        <?= Html::a(Yii::t('app/config', 'Create Entity Fields'), ['create'], ['class' => 'btn btn-success']) ?>
                        <?= Html::a(Yii::t('app/config', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                        <?= Html::a(Yii::t('app/config', 'Delete'), ['delete', 'id' => $model->id], [
                        'class' => 'btn btn-danger',
                        'data' => [
                        'confirm' => Yii::t('app/config', 'Are you sure you want to delete this item?'),
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
                                'alias',
                                [
                                    'attribute' => 'entity',
                                    'value' => $model->getEntityStr()
                                ],
                                [
                                    'attribute' => 'type',
                                    'value' => $model->getTypeStr()
                                ],
                                [
                                    'attribute' => 'required',
                                    'value' => $model->getRequiredStr()
                                ],
                                [
                                    'attribute' => 'validate',
                                    'value' => $model->getValidateStr()
                                ],
                                [
                                    'attribute' => 'options',
                                    'format' => 'html',
                                    'value' => is_array($model->options) ? implode('<br/>',$model->options) : $model->options
                                ],
                                [
                                    'attribute' => 'created_at',
                                    'value' => Yii::$app->formatter->asDatetime($model->created_at)
                                ],
                                [
                                    'attribute' => 'updated_at',
                                    'value' => Yii::$app->formatter->asDatetime($model->updated_at)
                                ]
                            ],
                        ]) ?>
                </div>
            </div>
        </div>
    </div>
