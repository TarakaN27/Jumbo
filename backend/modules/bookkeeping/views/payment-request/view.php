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
                                <i class = "fa fa-globe"></i> <?php echo Yii::t('app/common','Payment request')?>
                                <small class = "pull-right"><?echo Yii::$app->formatter->asDate($model->created_at);?></small>
                            </h1>
                        </div>
                    </div>
                    <div class = "row invoice-info">
                        <div class = "col-sm-4 invoice-col">
                            From
                             <address>
                                <strong></strong>
                                <br>795 Freedom Ave, Suite 600
                                 <br>New York, CA 94107
                                 <br>Phone: 1 (804) 123-9876
                                 <br>Email: ironadmin.com
                             </address>
                        </div>
                        <div class = "col-sm-4 invoice-col">
                            To
                            <address>
                                <strong>John Doe</strong>
                                    <br>795 Freedom Ave, Suite 600
                                    <br>New York, CA 94107
                                    <br>Phone: 1 (804) 123-9876
                                    <br>Email: jon@ironadmin.com
                            </address>
                        </div>
                        <div class = "col-sm-4 invoice-col">
                            <b>Invoice #007612</b>
                            <br>
                            <br>
                            <b>Order ID:</b> 4F3S8J
                            <br>
                            <b>Payment Due:</b> 2/22/2014
                            <br>
                            <b>Account:</b> 968-34567
                        </div>
                    </div>
                    <div class = "row">
                        <div class = "col-xs-12 table">
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
                        </div>
                    </div>
                    <div class = "row">
                        <div class = "col-xs-6">
                            <p class = "lead">Payment Methods:</p>
                            <img src = "images/visa.png" alt = "Visa">
                            <img src = "images/mastercard.png" alt = "Mastercard">
                            <img src = "images/american-express.png" alt = "American Express">
                            <img src = "images/paypal2.png" alt = "Paypal">
                            <p class = "text-muted well well-sm no-shadow" style = "margin-top: 10px;">
                                Etsy doostang zoodles disqus groupon greplin oooj voxy zoodles, weebly ning heekya handango imeem plugg dopplr jibjab, movity jajah plickers sifteo edmodo ifttt zimbra.
                            </p>
                        </div>
                        <div class = "col-xs-6">
                            <p class = "lead">Amount Due 2/22/2014</p>
                            <div class = "table-responsive">
                                <table class = "table">
                                    <tbody>
                                        <tr>
                                            <th style = "width:50%">Subtotal:</th>
                                            <td>$250.30</td>
                                        </tr>
                                        <tr>
                                            <th>Tax (9.3%)</th>
                                            <td>$10.34</td>
                                        </tr>
                                        <tr>
                                            <th>Shipping:</th>
                                            <td>$5.80</td>
                                        </tr>
                                        <tr>
                                            <th>Total:</th>
                                            <td>$265.24</td>
                                        </tr>
                                        </tbody>
                                    </table>
                                 </div>
                        </div>
                    </div>
                                        <!-- this row will not appear when printing -->
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