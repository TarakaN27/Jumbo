<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\CuserProspects */

$this->title = Yii::t('app/users', 'Update {modelClass}: ', [
    'modelClass' => 'Cuser Prospects',
]) . ' ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/users', 'Cuser Prospects'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app/users', 'Update');
?>
<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2><?= Html::encode($this->title) ?></h2>
                <section class="pull-right">
                    <?=  Html::a(Yii::t('app/users', 'To list'), ['index'], ['class' => 'btn btn-warning']) ?>
                </section>
                <div class="clearfix"></div>
            </div>
            <div class="x_content cuser-prospects-create">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
            </div>
        </div>
    </div>
</div>
