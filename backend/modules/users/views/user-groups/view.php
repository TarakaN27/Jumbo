<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\CUserGroups */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/users', 'Cuser Groups'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
    <div class = "row">
        <div class = "col-md-12 col-sm-12 col-xs-12">
            <div class = "x_panel">
                <div class = "x_title">
                    <h2><?= Html::encode($this->title) ?></h2>
                    <section class="pull-right">
                        <?=  Html::a(Yii::t('app/users', 'To list'), ['index'], ['class' => 'btn btn-warning']) ?>
                        <?= Html::a(Yii::t('app/users', 'Create Cuser Groups'), ['create'], ['class' => 'btn btn-success']) ?>
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
                                'id',
                                'name',
                                'created_at:datetime',
                                'updated_at:datetime',
                            ],
                        ]) ?>
                    <?=Html::tag('h3',Yii::t('app/crm','Group company'));?>
                    <?=\yii\grid\GridView::widget([
                            'dataProvider' => new \yii\data\ArrayDataProvider([
                               'allModels' => $arCuser,
                            ]),
                            'columns' => [
                                'id',
                                [
                                    'label' => Yii::t('app/users','Corp name'),
                                    'value' => function($model){
                                        return $model->getInfo();
                                    }
                                ],
                                [
                                    'attribute' => 'manager.fio',
                                    'label' => Yii::t('app/users','Manager ID')
                                ],
                                'contractor:boolean',
                            ]
                        ])?>
                </div>
            </div>
        </div>
    </div>
