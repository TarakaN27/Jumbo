<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\helpers\ArrayHelper;
/* @var $this yii\web\View */
/* @var $model common\models\ActFieldTemplate */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/book', 'Act Field Templates'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
    <div class = "row">
        <div class = "col-md-12 col-sm-12 col-xs-12">
            <div class = "x_panel">
                <div class = "x_title">
                    <h2><?= Html::encode($this->title) ?></h2>
                    <section class="pull-right">
                        <?=  Html::a(Yii::t('app/book', 'To list'), ['index'], ['class' => 'btn btn-warning']) ?>
                        <?= Html::a(Yii::t('app/book', 'Create Act Field Template'), ['create'], ['class' => 'btn btn-success']) ?>
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
                                    'attribute' => 'service_id',
                                    'value' => ArrayHelper::getValue($model,'service.name')
                                ],
                                [
                                    'attribute' => 'legal_id',
                                    'value' => ArrayHelper::getValue($model,'legal.name')
                                ],
                                'job_name:ntext',
								'job_name_eng:ntext',
                                'created_at:datetime',
                                'updated_at:datetime',
                            ],
                        ]) ?>
                </div>
            </div>
        </div>
    </div>
