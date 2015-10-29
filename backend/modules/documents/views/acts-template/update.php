<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\ActsTemplate */

$this->title = Yii::t('app/documents', 'Update {modelClass}: ', [
    'modelClass' => 'Acts Template',
]) . ' ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/documents', 'Acts Templates'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app/documents', 'Update');
?>
<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2><?= Html::encode($this->title) ?></h2>
                <section class="pull-right">
                    <?=  Html::a(Yii::t('app/documents', 'To list'), ['index'], ['class' => 'btn btn-warning']) ?>
                </section>
                <div class="clearfix"></div>
            </div>
            <div class="x_content acts-template-create">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
            </div>
        </div>
    </div>
</div>
