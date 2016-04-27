<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\search\PartnerExpenseCatLinkSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app/users', 'Partner Expense Cat Links');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class = "row">
    <div class = "col-md-12 col-sm-12 col-xs-12">
        <div class = "x_panel">
            <div class = "x_title">
                <h2><?= Html::encode($this->title) ?></h2>
                <section class="pull-right">
                    <?= Html::a(Yii::t('app/users', 'Create Partner Expense Cat Link'), ['create'], ['class' => 'btn btn-success']) ?>
                </section>
                <div class = "clearfix"></div>
            </div>
            <div class = "x_content">
                            <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],
                        [
                            'attribute' => 'id',
                             'format' => 'html',
                             'value' => function($model){
                                return Html::a($model->id,['update','id' => $model->id],['class' => 'link-upd']);
                             }
                        ],
                        [
                            'attribute' => 'type',
                            'value' => 'typeStr',
                            'filter' => \common\models\PartnerExpenseCatLink::getTypeMap()
                        ],
                        [
                            'attribute' => 'legal_person_id',
                            'value' => 'legalPerson.name',
                            'filter' => \common\models\LegalPerson::getLegalPersonMap()
                        ],
                        [
                            'attribute' => 'service_id',
                            'value' => 'service.name',
                            'filter' => \common\models\Services::getServicesMap()
                        ],
                        [
                            'attribute' => 'expanse_cat_id',
                            'value' => 'expanseCat.name',
                            'filter' => \common\models\ExpenseCategories::getExpenseCatMap()
                        ],
                        [
                            'attribute' => 'created_at',
                            'format' => 'datetime',
                            'filter' => FALSE
                        ],
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{view}'
                        ],
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{delete}'
                        ],
                    ],
                ]); ?>
                        </div>
        </div>
    </div>
</div>
