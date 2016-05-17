<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\CrmTask */

$this->title = Yii::t('app/crm', 'Update {modelClass}: ', [
    'modelClass' => 'Crm Task',
]) . ' ' . $model->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/crm', 'Crm Tasks'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app/crm', 'Update');
?>
<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2><?= Html::encode($this->title) ?></h2>
                <section class="pull-right">
                    <?=  Html::a(Yii::t('app/crm', 'To list'), ['index'], ['class' => 'btn btn-warning']) ?>
                </section>
                <div class="clearfix"></div>
            </div>
            <div class="x_content crm-task-create">
                <?= $this->render('_form', [
                    'model' => $model,
                    'cuserDesc' => $cuserDesc,
                    'contactDesc' => $contactDesc,
                    'sAssName' => $sAssName,
                    'data' => $data,
                    'pTaskName' => $pTaskName,
                    'dataWatchers' => $dataWatchers,
                    'obTaskRepeat' => $obTaskRepeat
                ]) ?>
            </div>
        </div>
    </div>
</div>
