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
    [
        'attribute' => 'amount',
        'format' => 'html',
        'value' => function($model){
            if($model->paid == \common\models\PromisedPayment::YES)
                return $model->amount;
            else{
                $obRepay = $model->repay;
                $paid = 0;
                foreach($obRepay as $rep)
                {
                    $paid+=$rep->amount;
                }
                return $model->amount.'<span class="pp_paid">/'.$paid.'</span>';
            }
        }
    ],
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
        'format' => 'datetime',
        'filter' => \kartik\date\DatePicker::widget([
            'model' => $searchModel,
            'attribute' => 'from_date',
            'attribute2' => 'to_date',
            'options' => ['placeholder' => Yii::t('app/crm','Begin date')],
            'options2' => ['placeholder' => Yii::t('app/crm','End date')],
            'type' => \kartik\date\DatePicker::TYPE_RANGE,
            'separator' => '-',
            'pluginOptions' => [
                //'autoclose' => true,
                'format' => 'dd.mm.yyyy',
                'defaultDate' => date('d.m.Y',time())
            ],
        ]),
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
                <div class="col-md-4 col-md-offset-8">
                    <?php if(!empty($arTotal)):?>
                        <?=Html::tag('h3',Yii::t('app/crm','Total'))?>
                        <table class="table table-striped table-bordered">
                            <?php foreach($arTotal as $key => $value):?>
                                <tr>
                                    <th><?=$key;?></th>
                                    <td><?=Yii::$app->formatter->asDecimal($value);?></td>
                                </tr>
                            <?php endforeach;?>
                        </table>
                    <?php endif;?>
                </div>
            </div>
        </div>
    </div>
</div>
