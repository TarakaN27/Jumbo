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
                <div class="col-ms-6">
                    <?php if(!empty($model->pr_payment_id)):?>
                        <?=Html::tag('h3','')?>

                    <?else:?>


                    <?php endif;?>

                </div>
            </div>
        </div>
    </div>
</div>
