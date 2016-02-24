<?php
/**
 * Created by PhpStorm.
 * Corp: ZM_TEAM
 * User: E. Motuz
 * Date: 2/24/16
 * Time: 4:15 PM
 */
use yii\helpers\Html;
$this->title = Yii::t('app/book','Process enrollment request')
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
                                'attribute' => 'amount',
                                'value' => $model->amount .' '.(is_object($obServ = $model->service) ? $obServ->enroll_unit : NULL)
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
                                ]
                            ]
                        ])?>
                    <?else:?>
                        <?=Html::tag('h3',Yii::t('app/book','Payment detail'))?>
                        <?= \yii\widgets\DetailView::widget([
                            'model' => $model,
                            'attributes' => [
                                'payment_id',
                                [
                                    'label' => Yii::t('app/book','Payment condition'),
                                    'value' => is_object($obCond) ? $obCond->name : NULL
                                ],
                                [
                                    'attribute' => 'pay_date',
                                    'value' => empty($model->pay_date) ? NULL : Yii::$app->formatter->asDatetime($model->pay_date)
                                ],
                                [
                                    'attribute' => 'pay_amount',
                                    'value' => $model->pay_amount.' '.(is_object($obCurr) ? $obCurr->code : '')
                                ],
                                [
                                    'label' => Yii::t('app/book','Production'),
                                    'value' => is_object($obCalc) ? $obCalc->production : NULL
                                ]
                            ]
                        ]);?>



                    <?php endif;?>

                </div>
                </div>
                <?php if($model->payment_id):?>
                <div class="row">
                    <?=Html::tag('h3',Yii::t('app/book','Promised payment'))?>
                    <?=\yii\grid\GridView::widget([
                        'dataProvider' => new \yii\data\ArrayDataProvider([
                              'allModels' => $arPromised
                          ])
                    ])?>

                </div>
                <?php endif;?>

                <div class="row">
                    <?php $form = \yii\bootstrap\ActiveForm::begin();?>
                        <?= $form->field($obForm,'enroll')->textInput();?>

                    <?php \yii\bootstrap\ActiveForm::end();?>
                </div>
            </div>
        </div>
    </div>
</div>
