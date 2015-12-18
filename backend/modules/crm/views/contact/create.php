<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\CrmCmpContacts */

$this->title = Yii::t('app/crm', 'Create Crm Cmp Contacts');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/crm', 'Crm Cmp Contacts'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
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
            <div class="x_content crm-cmp-contacts-create">
    <?= $this->render('_form', [
        'model' => $model,
        'cuserDesc' => $cuserDesc,
        'buserDesc' => $buserDesc
    ]) ?>
            </div>
        </div>
    </div>
</div>
