<?php

/* @var $this yii\web\View */
/* @var $model common\models\PaymentRequest */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/book', 'Payment Requests'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class = "row">
    <div class = "col-md-12">
        <div class = "x_panel">
            <div class = "x_content">
                <section class = "content invoice">
                    <div class = "row">
                        <div class = "col-xs-12 invoice-header">
                            <h1>
                                <i class = "fa fa-globe"></i> <?php echo Yii::t('app/book','Payment request')?>
                                <small class = "pull-right"><?echo Yii::$app->formatter->asDate($model->created_at);?></small>
                            </h1>
                        </div>
                    </div>
                    <div class = "row invoice-info">
                        <div class = "col-sm-4 invoice-col">
                            <?php echo Yii::t('app/book','From');?>
                            <?php $obCUser = $model->cuser;?>
                             <address>
                                <strong><?=is_object($obCUser) ? $obCUser->getInfo() : $model->user_name;?></strong>
                                <?php if($model->is_unknown == \common\models\PaymentRequest::YES)
                                    echo '<br>'.Yii::t('app/book','Contractor is unknown')
                                 ?>
                             </address>
                        </div>
                        <div class = "col-sm-4 invoice-col">
                            <?php echo Yii::t('app/book','To');?>
                            <?php if(is_object($obLegal = $model->legal)):?>
                                <address>
                                    <strong><?=$obLegal->name;?></strong>
                                </address>
                            <?php else:?>
                                <address>
                                    <strong><?php echo Yii::t('app/book','Not set legal person for payment')?></strong>
                                </address>
                            <?php endif;?>
                        </div>
                        <div class = "col-sm-4 invoice-col">
                            <b><?php echo Yii::t('app/book','Payment request')?> #<?=$model->id;?></b>
                            <br>
                            <br>
                            <b><?php echo Yii::t('app/book','Payment date')?>:</b> <?=Yii::$app->formatter->asDate($model->pay_date);?>
                        </div>
                        <div class = "col-sm-12 invoice-col">
                            <h4><?php echo Yii::t('app/book','Description')?></h4>
                            <?= empty($model->description) ? Yii::t('app/book','No description') : $model->description;?>
                        </div>
                    </div>
                    <div class = "row">
                        <div class = "col-xs-12 table">
                            <h4><?php echo Yii::t('app/book','Payments')?></h4>
                            <?php if($model->status == \common\models\PaymentRequest::STATUS_CANCELED):?>
                                <?php echo Yii::t('app/book','Payment request is canceled');?>
                            <?php elseif($model->status == \common\models\PaymentRequest::STATUS_FINISHED):?>
                                <?php
                                    $payments = $model->payments;
                                    if(!empty($payments)){
                                        echo \yii\grid\GridView::widget([
                                            'dataProvider' => new \yii\data\ArrayDataProvider([
                                                    'allModels' => $payments,
                                                    'pagination' => [
                                                      'pageSize' => 0,
                                                    ],
                                                ]),
                                            'tableOptions' => ['class' => 'table table-striped'],
                                            'columns' => [
                                                ['class' => 'yii\grid\SerialColumn'],
                                                'id',
                                                [
                                                    'attribute' => 'service_id',
                                                    'value' => function($model){
                                                            return is_object($obServ = $model->service) ? $obServ->name : 'N/A';
                                                        }
                                                ],
                                                'pay_summ',
                                                [
                                                    'attribute' => 'currency_id',
                                                    'value' => function($model){
                                                            return is_object($obCurr = $model->currency) ? $obCurr->code : 'N/A';
                                                        }
                                                ],
                                                'description'
                                            ]
                                        ]);
                                    }else{
                                       echo Yii::t('app/book','Payment is not found');
                                    }?>
                                <?php else:?>
                                    <?php echo Yii::t('app/book','Payment is not processed')?>
                                <?php endif;?>
                        </div>
                    </div>
                    <div class = "row no-print">
                        <div class = "col-xs-12">
                             <button class = "btn btn-default" onclick = "window.print();">
                                 <i class = "fa fa-print"></i> Print
                             </button>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>