<?php
/**
 * Created by PhpStorm.
 * Corp: ZM_TEAM
 * User: E. Motuz
 * Date: 2/24/16
 * Time: 4:15 PM
 */
use yii\helpers\Html;
use yii\web\JsExpression;
$this->title = Yii::t('app/book','Process enrollment request');

$this->registerJs("

function countDelta()
{
    var
            delta = 0,
            amount =  $('#enrollprocessform-availableamount').val(),
            repay = $('#enrollprocessform-repay').val(),
            enroll = $('#enrollprocessform-enroll');

        delta = amount-repay;
        if(delta < 0)
            {
                enroll.val(0);
            }else{
                enroll.val(delta);
            }
}

$('#enrollprocessform-repay').on('change',countDelta);
$('#enrollprocessform-cuserop').on('change',function(){
        var
            this1 = $(this),
            value = this1.val();

        $.ajax({
            type: 'POST',
            url: '".\yii\helpers\Url::to(['get-promised-payment'])."',
            data: { cuserID: ".$model->service_id.", cuserOP: value,servID: ".$model->service_id." },
            success: function(data){
                $('#promised-payment-table').html(data.grid);
                var ppAmount = parseFloat(data.amount);
                if(ppAmount > 0)
                {
                    var
                        amount =  parseFloat($('#enrollprocessform-availableamount').val()),
                        repay = $('#enrollprocessform-repay');
                    repay.removeAttr('disabled');

                    if(amount > ppAmount)
                    {
                        repay.val(ppAmount);
                    }else{
                        repay.val(amount);
                    }
                    countDelta();
                }
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                addErrorNotify('".Yii::t('app/reports','Promised payment')."','".Yii::t('app/reports','Can not load promised payments')."');
                this1.val('');
                var
                    repay = $('#enrollprocessform-repay');

                repay.val(0);
                repay.attr('disabled','disabled');
                countDelta();
            }
        });
});
",\yii\web\View::POS_READY);

?>
<div class = "row">
    <div class = "col-md-12 col-sm-12 col-xs-12">
        <div class = "x_panel">
            <div class = "x_title">
                <h2><?= Html::encode($this->title) ?></h2>
                <section class="pull-right">
                </section>
                <div class = "clearfix"></div>
            </div>
            <div class = "x_content">
                <div class="row">
                <div class="col-md-6">
                    <?=Html::tag('h3',Yii::t('app/book','Enrollment request detail'))?>
                    <?=\yii\widgets\DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            [
                                'attribute' => 'service_id',
                                'value' =>is_object($obServ = $model->service) ? $obServ->name : NULL
                            ],
                            [
                                'attribute' => 'cuser_id',
                                'value' => is_object($obCuser = $model->cuser) ? $obCuser->getInfo() : NULL
                            ],
                            [
                                'attribute' => 'assigned_id',
                                'value' => is_object($obBuser = $model->assigned) ? $obBuser->getFio() : NULL
                            ],
                            [
                                'attribute' => 'created_at',
                                'value' => !empty($model->created_at) ? Yii::$app->formatter->asDatetime($model->created_at) : NULL
                            ],
                            [
                                'attribute' => 'added_by',
                                'value' => is_object($obAUser = $model->added) ? $obAUser->getFio() : NULL
                            ],
                            'parent_id',
                            [
                                'attribute' => 'amount',
                                'label' => Yii::t('app/book','Counting unit amount'),
                                'value' => Yii::$app->formatter->asDecimal($model->amount) .' '.(is_object($obServ = $model->service) ? $obServ->enroll_unit : NULL)
                            ],
                        ]
                    ])?>
                </div>
                <div class="col-md-6">
                    <?php if(!empty($model->pr_payment_id)):?>
                        <?=Html::tag('h3',Yii::t('app/book','Promised payment detail'))?>
                        <?=\yii\widgets\DetailView::widget([
                            'model' => $obPrPay,
                            'attributes' => [
                                'amount',
                                [
                                    'attribute' => 'created_at',
                                    'value' => Yii::$app->formatter->asDatetime($obPrPay->created_at)
                                ],
                                'description:text'
                            ]
                        ])?>
                    <?else:?>
                        <?=Html::tag('h3',Yii::t('app/book','Payment detail'))?>
                        <?= \yii\widgets\DetailView::widget([
                            'model' => $model,
                            'attributes' => [
                                [
                                    'attribute' => 'payment_id',
                                    'label' => Yii::t('app/book','PaymentID')
                                ],
                                [
                                    'label' => Yii::t('app/book','Payment condition'),
                                    'value' => is_object($obCond) ? $obCond->name : NULL
                                ],
                                [
                                    'attribute' => 'pay_date',
                                    'value' => empty($model->pay_date) ? NULL : Yii::$app->formatter->asDate($model->pay_date)
                                ],
                                [
                                    'attribute' => 'pay_amount',
                                    'value' => Yii::$app->formatter->asDecimal($model->pay_amount).' '.(is_object($obCurr) ? $obCurr->code : '')
                                ],
                                [
                                    'label' => Yii::t('app/book','Production'),
                                    'value' => is_object($obCalc) ? Yii::$app->formatter->asDecimal($obCalc->production).' BYR'. ' <'.Yii::$app->formatter->asDecimal($exchRate).'>' : NULL
                                ],
                                [
                                    'label' => Yii::t('app/book','Description'),
                                    'value' => is_object($obPayment) ? $obPayment->description : NULL
                                ],
                                [
                                    'label' => Yii::t('app/book','Legal person'),
                                    'value' => !is_object($obPayment) ? NULL : is_object($obLegal = $obPayment->legal) ? $obLegal->name : NULL
                                ],
                                [
                                    'label' => Yii::t('app/book','Is residen'),
                                    'value' => is_object($obCond) ? $obCond->getYesnoStr($obCond->is_resident) : NULL
                                ]
                            ]
                        ]);?>
                    <?php endif;?>
                </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <?php if($model->payment_id):?>
                            <?=Html::tag('h3',Yii::t('app/book','Promised payments'))?>
                            <div id="promised-payment-table">
                                <?=$this->render('_promised_grid',['arPromised' => $arPromised]);?>
                            </div>
                        <?php endif;?>
                    </div>
                </div>

                <div class="row">
                        <?=Html::tag('h3',Yii::t('app/book','Enroll request proccess'))?>
                        <?php
                        $form = \yii\bootstrap\ActiveForm::begin([
                            'options' => [
                                'class' => 'form-horizontal form-label-left'
                            ],
                            'fieldConfig' => [
                                'template' => '<div class="form-group">{label}<div class="col-md-6 col-sm-6 col-xs-12">{input}</div><ul class="parsley-errors-list" >{error}</ul></div>',
                                'labelOptions' => ['class' => 'control-label col-md-3 col-sm-3 col-xs-12'],
                            ],
                        ]);
                        echo Html::activeHiddenInput($obForm,'availableAmount');

                        ?>
                        <?= $form->field($obForm,'enroll')->textInput();?>
                        <?php
                            if($obForm->isPayment)
                            {
                                $arOptions = [];
                                if(is_null($arPromised) || count($arPromised) == 0)
                                    $arOptions['disabled'] = 'disabled';

                                echo $form->field($obForm,'repay')->textInput($arOptions);

                                echo $form->field($obForm,'cuserOP')->widget(\kartik\select2\Select2::className(),[
                                    'initValueText' => $cuserDesc, // set the initial display text
                                    'options' => [
                                        'placeholder' => Yii::t('app/crm','Search for a company ...')
                                    ],
                                    'pluginOptions' => [
                                        'allowClear' => true,
                                        'minimumInputLength' => 2,
                                        'ajax' => [
                                            'url' => \yii\helpers\Url::to(['/ajax-select/get-contractor']),
                                            'dataType' => 'json',
                                            'data' => new JsExpression('function(params) { return {q:params.term}; }')
                                        ],
                                        'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                                        'templateResult' => new JsExpression('function(cmp_id) { return cmp_id.text; }'),
                                        'templateSelection' => new JsExpression('function (cmp_id) { return cmp_id.text; }'),
                                    ],
                                ]);
                            }
                        ?>
                        <div class="form-group">
                            <div class = "col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                                <?= $form->field($obForm,'part_enroll')->checkbox()?>
                            </div>
                        </div>

                        <?= $form->field($obForm,'description')->textarea()?>

                        <div class="form-group">
                            <div class = "col-md-offset-8 ">
                                <?= Html::submitButton(Yii::t('app/book', 'Processing'), ['class' => 'btn btn-success']) ?>
                            </div>
                        </div>
                        <?php \yii\bootstrap\ActiveForm::end();?>
                </div>
            </div>
        </div>
    </div>
</div>
