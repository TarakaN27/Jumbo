<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\search\PromisedPaymentSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app/book', 'Promised Payments');
$this->params['breadcrumbs'][] = $this->title;

$gridView = [
    ['class' => 'yii\grid\SerialColumn'],
    'amount',
    [
        'attribute' => 'paid_date',
        'format' => 'html',
        'value' => function($model){
            return Yii::$app->formatter->asDatetime($model->paid_date);
        }
    ],
    [
        'attribute' => 'paid',
        'format' => 'html',
        'value' => function($model){
            return $model->getYesNo($model->paid);
        }
    ],
    [
        'attribute' => 'created_at',
        'format' => 'hrml',
        'value' => function($model){
            return Yii::$app->formatter->asDatetime($model->created_at);
        }
    ],
    ['class' => 'yii\grid\ActionColumn'],
];

if(Yii::$app->user->isManager())
{
    $gridView  [] = 'cuser_id';
    $gridView  [] = 'buser_id_p';
}

?>
<div class = "row">
    <div class = "col-md-12 col-sm-12 col-xs-12">
        <div class = "x_panel">
            <div class = "x_title">
                <h2><?php echo $this->title?></h2>
                <section class="pull-right">
                    <?= Html::a(Yii::t('app/book', 'Create Promised Payment'), ['create'], ['class' => 'btn btn-success']) ?>
                </section>
                <div class = "clearfix"></div>
            </div>
            <div class = "x_content">

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => $gridView,
    ]); ?>
            </div>
        </div>
    </div>
</div>
