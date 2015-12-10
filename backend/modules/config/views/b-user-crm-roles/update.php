<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\BUserCrmRoles */

$this->title = Yii::t('app/config', 'Update {modelClass}: ', [
    'modelClass' => 'Buser Crm Roles',
]) . ' ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/config', 'Buser Crm Roles'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app/config', 'Update');
?>
<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2><?= Html::encode($this->title) ?></h2>
                <section class="pull-right">
                    <?=  Html::a(Yii::t('app/config', 'To list'), ['index'], ['class' => 'btn btn-warning']) ?>
                </section>
                <div class="clearfix"></div>
            </div>
            <div class="x_content buser-crm-roles-create">
                <?= $this->render('_form', [
                    'model' => $model,
                    'modelRule' => $modelRule
                ]) ?>
            </div>
        </div>
    </div>
</div>
