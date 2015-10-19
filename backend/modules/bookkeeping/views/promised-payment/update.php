<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\PromisedPayment */

$this->title = Yii::t('app/book', 'Update {modelClass}: ', [
    'modelClass' => 'Promised Payment',
]) . ' ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/book', 'Promised Payments'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app/book', 'Update');
?>
<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2><?= Html::encode($this->title) ?><small><?=$model->id?></small></h2>
                <section class="pull-right">
                    <?= Html::a(Yii::t('app/book', 'To list'), ['index'], ['class' => 'btn btn-warning']) ?>
                </section>
                <div class="clearfix"></div>
            </div>
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

        </div></div></div>
