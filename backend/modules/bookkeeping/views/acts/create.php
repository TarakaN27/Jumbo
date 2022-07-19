<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\Acts */
use yii\bootstrap\Modal;

$this->title = Yii::t('app/book', 'Create Acts');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/book', 'Acts'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2><?= Html::encode($this->title) ?></h2>
                <section class="pull-right">
                    <?=  Html::a(Yii::t('app/book', 'To list'), ['index'], ['class' => 'btn btn-warning']) ?>
                </section>
                <div class="clearfix"></div>
            </div>
            <div class="x_content acts-create">
                <?= $this->render('_form_v2', [
                    'model' => $model,
                    'contractorInitText' => $contractorInitText
                ]) ?>
            </div>
        </div>
        <?php
        Modal::begin([
            'header' => '<h2>Пустой платеж</h2>',
            'id'=>'modalEmptyForm',
        ]); ?>
        <?= $this->render('_payment_request_form', [
            'modelEmpty'=>$modelEmpty
        ]) ?>
        <? Modal::end(); ?>
    </div>
</div>
