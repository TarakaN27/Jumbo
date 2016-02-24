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
        'attribute' => 'service_id',
        'value' => function($model){
            return is_object($obServ = $model->service) ? $obServ->name : 'N/A';
        },
        'filter' => \common\models\Services::getServicesMap()
    ],
    [
        'attribute' => 'paid_date',
        'format' => 'html',
        'value' => function($model){
            return Yii::$app->formatter->asDatetime($model->paid_date);
        }
    ],
    [
        'attribute' => 'paid',
        'format' => 'raw',
        'value' => function($model){
            return Html::tag('i','',[
                'class' => "fa fa-check-circle " . ($model->paid ? "paid-green" : "paid-red"),
                'data' => $model->paid ? 1 : 0,
                'data-id' => $model->id
            ]);
        },
        'filter' => \common\models\PromisedPayment::getYesNo(),
        'contentOptions' => ['class' => 'text-center'],
    ],
    [
        'attribute' => 'created_at',
        'format' => 'html',
        'value' => function($model){
            return Yii::$app->formatter->asDatetime($model->created_at);
        }
    ],
];

if(!Yii::$app->user->isManager())
{
    $gridView  [] = [
        'attribute' => 'cuser_id',
        'value' => function($model){
            return is_object($obUser = $model->cuser) ? $obUser->getInfo() : 'N/A';
        }
    ];
    $gridView  [] = [
        'attribute' => 'buser_id_p',
        'value' => function($model){
            return is_object($obBuser = $model->buser) ? $obBuser->getFio() : 'N/A';
        }
    ];
    $gridView  [] = [
        'class' => 'yii\grid\ActionColumn',
        'template' => '{view}'
    ];
}
/*
$this->registerJs("
    $('table').on('click','.paid-control',function(){
        var
            id = $(this).attr('data-id'),
            paidControl = $('.paid-control[data-id = \"'+id+'\"]');
        console.log(id);
        $.ajax({
       type: \"POST\",
       url: '".\yii\helpers\Url::to(['change-paid'])."',
       data: { pk: id},
       dataType: 'json',
       success: function(msg){
            if(msg == 1)
            {
                paidControl.removeClass('paid-red');
                paidControl.addClass('paid-green');
            }else{
                paidControl.removeClass('paid-green');
                paidControl.addClass('paid-red');
            }
       },
       error: function(err){
        alert('Error');
       }
     });
    });
",\yii\web\View::POS_READY);
*/
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
