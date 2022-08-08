<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use common\components\helpers\CustomHelper;
/* @var $this yii\web\View */
/* @var $model common\models\Bills */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/documents', 'Bills'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

if(isset($model->bank_id)) {
	$bank = common\models\BankDetails::findOne($model->bank_id);
	$bank_req = $bank ? $bank->bank_details_act: 'N/A';
}

?>
<div class = "row">
    <div class = "col-md-12 col-sm-12 col-xs-12">
        <div class = "x_panel">
            <div class = "x_title">
                <h2><?= Html::encode($this->title) ?></h2>
                <section class="pull-right">
                    <?= Html::a(Yii::t('app/documents', 'To list'), ['index'], ['class' => 'btn btn-warning']) ?>
                    <?= Html::a('<i class="fa fa-file-pdf-o"></i> '.Yii::t('app/documents', 'Get bill'),[
                        'get-bill',
                        'type' => \common\models\Bills::TYPE_DOC_PDF,
						'lang' => 0,
                        'id' => $model->id
                    ],
                        [
                            'target' => '_blank',
                            'class' => 'btn btn-warning'
                        ]); ?>
					<?= Html::a('<i class="fa fa-file-pdf-o"></i> '.Yii::t('app/documents', 'Get bill Eng'),[
                        'get-bill',
                        'type' => \common\models\Bills::TYPE_DOC_PDF,
                        'lang' => 1,
                        'id' => $model->id
                    ],
                        [
                            'target' => '_blank',
                            'class' => 'btn btn-warning'
                        ]); ?>
                    <?= Html::a(Yii::t('app/documents','Create Bill'),['create'],['class'=>'btn btn-primary']);?>
                    <?= Html::a(Yii::t('app/documents', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                    <?php if(!Yii::$app->user->can('only_bookkeeper')):?>
                    <?= Html::a(Yii::t('app/documents', 'Delete'), ['delete', 'id' => $model->id], [
                        'class' => 'btn btn-danger',
                        'data' => [
                            'confirm' => Yii::t('app/documents', 'Are you sure you want to delete this item?'),
                            'method' => 'post',
                        ],
                    ]) ?>
                    <?php endif;?>
                </section>
                <div class = "clearfix"></div>
            </div>
            <div class = "x_content">
                <?= DetailView::widget([
                    'model' => $model,
                    'attributes' => [
                        'id',
                        [
                            'attribute' => 'cuser_id',
                            'value' => is_object($obCUser = $model->cuser) ? $obCUser->getInfo() : 'N/A'
                        ],
                        [
                            'attribute' => 'l_person_id',
                            'value' => is_object($obLP = $model->lPerson) ? $obLP -> name : 'N/A'
                        ],
						[
                            'attribute' => 'bank_id',
                            'value' => $bank_req
                        ],
                        [
                            'attribute' => 'service_id',
                            'value' => is_object($obServ = $model->service) ? $obServ->name : 'N/A'
                        ],
                        [
                            'attribute' => 'docx_tmpl_id',
                            'value' => is_object($obDocx = $model->docxTmpl) ? $obDocx->name : 'N/A'
                        ],
                        [
                            'attribute' => 'amount',
                            'value' => Yii::$app->formatter->asDecimal($model->amount).' ( '.
                                CustomHelper::numPropis((int)$model->amount).' ) ' .\common\models\Bills::getCurrencyById($model->curr_id)
                        ],
                        'bill_number',
                        'bill_date:date',
                        [
                            'attribute' => 'bill_template',
                            'value' => is_object($obTmpl = $model->bTemplate) ? $obTmpl->name : 'N/A'
                        ],
                        [
                            'attribute' => 'use_vat',
                            'value' => $model->getYesNoStr($model->use_vat)
                        ],
                        'vat_rate:decimal',
                        'description:ntext',
                        'object_text:ntext',
                        'offer_contract',
                        'buy_target',
						[
							'attribute' => 'period_date',
							'value' => Yii::$app->formatter->asDate($model->period_date, 'php:F Y')
						],
                        [
                            'attribute' => 'created_at',
                            'value' => Yii::$app->formatter->asDatetime($model->created_at)
                        ],
                        [
                            'attribute' => 'updated_at',
                            'value' => Yii::$app->formatter->asDatetime($model->updated_at)
                        ],
                    ],
                ]);

				?>

                <?php
                    echo \yii\grid\GridView::widget([
                        'dataProvider' => new \yii\data\ActiveDataProvider([
                            'query' => $model->getBillServices(),
                            'pagination' => [
                                'defaultPageSize' => 1000,
                                'pageSizeLimit' => [1,1000]
                            ],
                        ]),
                        'columns' => [
                            'id',
                            [
                                'attribute' => 'service_id',
                                'value' => 'service.name'
                            ],
                            'amount:decimal',
                            'serv_title:ntext',
                            'description:ntext',
                            'offer',
                            'ordering'
                        ]
                    ]);
                ?>
            </div>
        </div>
    </div>
</div>
