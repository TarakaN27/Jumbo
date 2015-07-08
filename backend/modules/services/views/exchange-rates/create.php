<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\ExchangeRates */

$this->title = Yii::t('app/services', 'Create Exchange Rates');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/services', 'Exchange Rates'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="clearfix"></div>
                    <div class="row">
                        <div class="col-md-12 col-sm-12 col-xs-12">
                            <div class="x_panel">
                                <div class="x_title">
                                    <h2><?= Html::encode($this->title) ?><small>форма добавления валют</small></h2>
                                     <section class="pull-right">
                                    <?= Html::a(Yii::t('app/services', 'To list'), ['index'], ['class' => 'btn btn-warning']) ?>
                                    </section>
                                    <div class="clearfix"></div>
                                </div>
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div></div></div>
