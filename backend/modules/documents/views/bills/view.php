<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use common\components\helpers\CustomHelper;
/* @var $this yii\web\View */
/* @var $model common\models\Bills */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/documents', 'Bills'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class = "row">
    <div class = "col-md-12 col-sm-12 col-xs-12">
        <div class = "x_panel">
            <div class = "x_title">
                <h2><?= Html::encode($this->title) ?></h2>
                <section class="pull-right">
                    <?= Html::a(Yii::t('app/documents', 'To list'), ['index'], ['class' => 'btn btn-warning']) ?>
                    <?= Html::a(Yii::t('app/documents','Create Bill'),['create'],['class'=>'btn btn-primary']);?>
                    <?= Html::a(Yii::t('app/documents', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                    <?= Html::a(Yii::t('app/documents', 'Delete'), ['delete', 'id' => $model->id], [
                        'class' => 'btn btn-danger',
                        'data' => [
                            'confirm' => Yii::t('app/documents', 'Are you sure you want to delete this item?'),
                            'method' => 'post',
                        ],
                    ]) ?>
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
                            'attribute' => 'service_id',
                            'value' => is_object($obServ = $model->service) ? $obServ->name : 'N/A'
                        ],
                        [
                            'attribute' => 'docx_tmpl_id',
                            'value' => is_object($obDocx = $model->docxTmpl) ? $obDocx->name : 'N/A'
                        ],
                        [
                            'attribute' => 'amount',
                            'value' => $model->amount.' ( '.
                                CustomHelper::numPropis($model->amount).' '.
                                CustomHelper::ciRub($model->amount).' )'
                        ],
                        'bill_number',
                        'bill_date',
                        [
                            'attribute' => 'bill_template',
                            'value' => is_object($obTmpl = $model->bTemplate) ? $obTmpl->name : 'N/A'
                        ],
                        [
                            'attribute' => 'use_vat',
                            'value' => $model->getYesNoStr($model->use_vat)
                        ],
                        'vat_rate',
                        'description:ntext',
                        'object_text:ntext',
                        'buy_target',
                        [
                            'attribute' => 'created_at',
                            'value' => Yii::$app->formatter->asDatetime($model->created_at)
                        ],
                        [
                            'attribute' => 'updated_at',
                            'value' => Yii::$app->formatter->asDatetime($model->updated_at)
                        ],
                    ],
                ]) ?>
            </div>
        </div>
    </div>
</div>
