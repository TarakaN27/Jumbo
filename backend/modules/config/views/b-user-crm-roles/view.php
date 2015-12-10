<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\BUserCrmRoles */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/config', 'Buser Crm Roles'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
    <div class = "row">
        <div class = "col-md-12 col-sm-12 col-xs-12">
            <div class = "x_panel">
                <div class = "x_title">
                    <h2><?= Html::encode($this->title) ?></h2>
                    <section class="pull-right">
                        <?=  Html::a(Yii::t('app/config', 'To list'), ['index'], ['class' => 'btn btn-warning']) ?>
                        <?= Html::a(Yii::t('app/config', 'Create Buser Crm Roles'), ['create'], ['class' => 'btn btn-success']) ?>
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
                    <h3><?=Yii::t('app/crm','Rules')?>:</h3>
                        <?=\yii\grid\GridView::widget([
                            'dataProvider' => new \yii\data\ArrayDataProvider([
                                'allModels' => $arRules,
                                'pagination' => [
                                      'pageSize' => -1,
                                  ],
                            ]),
                            'columns' => [
                                [
                                    'attribute' => 'crt',
                                    'value' => function($model)
                                    {
                                        return $model->getCreateStr();
                                    }
                                ],
                                [
                                    'attribute' => 'rd',
                                    'value' => function($model)
                                    {
                                        return $model->getReadStr();
                                    }
                                ],
                                [
                                    'attribute' => 'upd',
                                    'value' => function($model)
                                    {
                                        return $model->getUpdateStr();
                                    }
                                ],
                                [
                                    'attribute' => 'del',
                                    'value' => function($model)
                                    {
                                        return $model->getDeleteStr();
                                    }
                                ],
                            ],
                        ])?>
                </div>
            </div>
        </div>
    </div>
