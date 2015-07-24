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
                                <table class = "table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Qty</th>
                                            <th>Product</th>
                                            <th>Serial #</th>
                                            <th style = "width: 59%">Description</th>
                                            <th>Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>1</td>
                                            <td>Call of Duty</td>
                                            <td>455-981-221</td>
                                            <td>El snort testosterone trophy driving gloves handsome gerry Richardson helvetica tousled street art master testosterone trophy driving gloves handsome gerry Richardson
                                            </td>
                                            <td>$64.50</td>
                                        </tr>
                                        <tr>
                                            <td>1</td>
                                            <td>Need for Speed IV</td>
                                            <td>247-925-726</td>
                                            <td>Wes Anderson umami biodiesel</td>
                                            <td>$50.00</td>
                                        </tr>
                                        <tr>
                                            <td>1</td>
                                            <td>Monsters DVD</td>
                                            <td>735-845-642</td>
                                            <td>Terry Richardson helvetica tousled street art master, El snort testosterone trophy driving gloves handsome letterpress erry Richardson helvetica tousled</td>
                                            <td>$10.70</td>
                                        </tr>
                                        <tr>
                                            <td>1</td>
                                            <td>Grown Ups Blue Ray</td>
                                            <td>422-568-642</td>
                                            <td>Tousled lomo letterpress erry Richardson helvetica tousled street art master helvetica tousled street art master, El snort testosterone</td>
                                            <td>$25.99</td>
                                        </tr>
                                    </tbody>
                                </table>
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
                             <button class = "btn btn-success pull-right">
                                 <i class = "fa fa-credit-card"></i> Submit Payment
                             </button>
                             <button class = "btn btn-primary pull-right" style = "margin-right: 5px;">
                                 <i class = "fa fa-download"></i> Generate PDF
                             </button>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>