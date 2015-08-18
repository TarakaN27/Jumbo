<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Units */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/units', 'Units'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class = "row">
    <div class = "col-md-12 col-sm-12 col-xs-12">
        <div class = "x_panel">
            <div class = "x_title">
                <h2><?= Html::encode($this->title) ?></h2>
                <section class="pull-right">
                    <?= Html::a(Yii::t('app/services', 'To list'), ['index'], ['class' => 'btn btn-warning']) ?>
                    <?php if(Yii::$app->user->can('adminRights')): ?>
                        <?= Html::a(Yii::t('app/services','Create Services'),['create'],['class'=>'btn btn-primary']);?>
                        <?= Html::a(Yii::t('app/services', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
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
                        [
                            'attribute' => 'type',
                            'value' => $model->getTypeStr()
                        ],
                        [
                            'attribute' => 'service_id',
                            'value' => is_object($obServ = $model->service) ? $obServ->name : 'N/A'
                        ],
                        'cost',
                        [
                            'attribute' => 'cuser_id',
                            'value' => is_object($obCuser = $model->cuser) ? $obCuser->getInfo() : 'N/A'
                        ],
                        [
                            'attribute' => 'multiple',
                            'value' => $model->getYesNoStr($model->multiple)
                        ],
                        'created_at',
                        'updated_at',
                    ],
                ]) ?>
            </div>
        </div>
    </div>
</div>
