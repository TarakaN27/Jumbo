<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\PartnerExpenseCatLink */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/users', 'Partner Expense Cat Links'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
    <div class = "row">
        <div class = "col-md-12 col-sm-12 col-xs-12">
            <div class = "x_panel">
                <div class = "x_title">
                    <h2><?= Html::encode($this->title) ?></h2>
                    <section class="pull-right">
                        <?=  Html::a(Yii::t('app/users', 'To list'), ['index'], ['class' => 'btn btn-warning']) ?>
                        <?= Html::a(Yii::t('app/users', 'Create Partner Expense Cat Link'), ['create'], ['class' => 'btn btn-success']) ?>
                        <?= Html::a(Yii::t('app/users', 'Delete'), ['delete', 'id' => $model->id], [
                                'class' => 'btn btn-danger',
                                'data' => [
                                'confirm' => Yii::t('app/users', 'Are you sure you want to delete this item?'),
                                'method' => 'post',
                            ],
                        ]) ?>
                    </section>
                    <div class = "clearfix"></div>
                </div>
                <div class = "x_content">
                        <?= DetailView::widget([
                            'model' => $model,
                            'attributes' => [
                                'id',
                                [
                                    'attribute' => 'type',
                                    'value' => $model->getTypeStr()
                                ],
                                [
                                    'attribute' => 'legal_person_id',
                                    'value' => is_object($obLP = $model->legalPerson) ? $obLP->name : NULL,
                                ],
                                [
                                    'attribute' => 'service_id',
                                    'value' => is_object($obServ = $model->service) ? $obServ->name : NULL,
                                ],
                                [
                                    'attribute' => 'expanse_cat_id',
                                    'value' => is_object($obCat = $model->expanseCat) ? $obCat->name : NULL,
                                ],
                                'created_at:datetime',
                                'updated_at:datetime',
                            ],
                        ]) ?>
                </div>
            </div>
        </div>
    </div>
