<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model backend\models\BUser */

$this->title = Yii::t('app/users', 'Create Buser');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/users', 'Busers'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
                    <div class="clearfix"></div>
                    <div class="row">
                        <div class="col-md-12 col-sm-12 col-xs-12">
                            <div class="x_panel">
                                <div class="x_title">
                                    <h2>Сотдрудники <small>форма добавления сотрудников(администратор, бухгалтер, менеджер)</small></h2>
                                    <section class="pull-right">
                                    <?= Html::a(Yii::t('app/users', 'To list'), ['index'], ['class' => 'btn btn-warning']) ?>
                                    </section>
                                    <div class="clearfix"></div>
                                </div>
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div></div></div>
