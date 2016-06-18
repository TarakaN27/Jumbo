<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Bills */


$id = isset($billModel) ? $billModel->id : $model->id;

$this->title = Yii::t('app/documents', 'Update {modelClass}: ', [
    'modelClass' => 'Bills',
]) . ' ' . $id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/documents', 'Bills'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $id, 'url' => ['view', 'id' => $id]];
$this->params['breadcrumbs'][] = Yii::t('app/documents', 'Update');
?>
<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2><?= Html::encode($this->title) ?><small><?=$id?></small></h2>
                <section class="pull-right">
                    <?= Html::a(Yii::t('app/documents', 'To list'), ['index'], ['class' => 'btn btn-warning']) ?>
                </section>
                <div class="clearfix"></div>
            </div>
            <?= $this->render($view_form, [
                'model' => $model,
                'cuserDesc' => $cuserDesc,
                'arServices' => $arServices
            ]) ?>
        </div>
    </div>
</div>
