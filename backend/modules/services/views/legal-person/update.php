<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\LegalPerson */

$this->title = Yii::t('app/services', 'Update {modelClass}: ', [
    'modelClass' => 'Legal Person',
]) . ' ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/services', 'Legal People'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app/services', 'Update');
?>
?>
<div class="clearfix"></div>
                    <div class="row">
                        <div class="col-md-12 col-sm-12 col-xs-12">
                            <div class="x_panel">
                                <div class="x_title">
                                    <h2><?= Html::encode($this->title) ?><small>форма добавления юр. адреса</small></h2>
                                     <section class="pull-right">
                                    <?= Html::a(Yii::t('app/book', 'To list'), ['index'], ['class' => 'btn btn-warning']) ?>
                                    </section>
                                    <div class="clearfix"></div>
                                </div>
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div></div></div>
