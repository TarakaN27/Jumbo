<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\search\PartnerWithdrawalSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app/book', 'Partner Withdrawals');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class = "row">
    <div class = "col-md-12 col-sm-12 col-xs-12">
        <div class = "x_panel">
            <div class = "x_title">
                <h2><?= Html::encode($this->title) ?></h2>
                <section class="pull-right">
                    <?= Html::a(Yii::t('app/book', 'Create Partner Withdrawal'), ['create'], ['class' => 'btn btn-success']) ?>
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
                            'attribute' => 'partner_id',
                            'format' => 'html',
                            'value' => function ($model){
                                $str = is_object($obP = $model->partner) ? $obP->getFio() : $model->partner_id;
                                return Html::a($str,['update','id' => $model->id],['class' => 'link-upd']);
                            },
                            'filter' => \common\models\Partner::getPartnerMap()
                        ],
                        'amount',
                        [
                            'attribute' => 'type',
                            'value' => function($model){
                                return $model->getTypeStr();
                            },
                            'filter' => \common\models\PartnerWithdrawal::getTypeArr()
                        ],
                        'description:ntext',
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
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{update}{view}'
                        ],
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{delete}'
                        ],
                    ],
                ]); ?>
                <div class="col-md-4 ">
                    <?php if(!empty($iTotal)):?>
                        <?=Html::tag('h3',Yii::t('app/crm','Total').' <small>'.$iTotal.'</small>')?>
                    <?php endif;?>
                </div>
            </div>
        </div>
    </div>
</div>
