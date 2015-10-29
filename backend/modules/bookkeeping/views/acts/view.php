<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\Acts */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/book', 'Acts'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
    <div class = "row">
        <div class = "col-md-12 col-sm-12 col-xs-12">
            <div class = "x_panel">
                <div class = "x_title">
                    <h2><?= Html::encode($this->title) ?></h2>
                    <section class="pull-right">
                        <?=  Html::a(Yii::t('app/book', 'To list'), ['index'], ['class' => 'btn btn-warning']) ?>
                        <?= Html::a(Yii::t('app/book', 'Create Acts'), ['create'], ['class' => 'btn btn-success']) ?>
                        <?= Html::a(Yii::t('app/book', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                        <?= Html::a(Yii::t('app/book', 'Delete'), ['delete', 'id' => $model->id], [
                        'class' => 'btn btn-danger',
                        'data' => [
                        'confirm' => Yii::t('app/book', 'Are you sure you want to delete this item?'),
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
                                [
                                    'attribute' => 'cuser_id',
                                    'value' => is_object($obCuser = $model->cuser) ? $obCuser->getInfo() : $model->cuser_id
                                ],
                                [
                                    'attribute' => 'buser_id',
                                    'value' => is_object($obBuser = $model->buser) ? $obBuser->getFio() : $model->buser_id
                                ],
                                [
                                    'attribute' => 'service_id',
                                    'value' => is_object($obServ = $model->service) ? $obServ->name : $model->service_id
                                ],
                                [
                                    'attribute' => 'template_id',
                                    'value' => is_object($obTmpl = $model->template) ? $obTmpl->name : $model->template_id
                                ],
                                'amount',
                                'act_date',
                                [
                                    'attribute' => 'sent',
                                    'value' => $model->getYesNoStr($model->sent)
                                ],
                                [
                                    'attribute' => 'change',
                                    'value' => $model->getYesNoStr($model->change)
                                ],
                                [
                                    'attribute' => 'created_at',
                                    'format' => 'html',
                                    'value' => Yii::$app->formatter->asDatetime($model->created_at)
                                ],
                                [
                                    'attribute' => 'updated_at',
                                    'format' => 'html',
                                    'value' => Yii::$app->formatter->asDatetime($model->updated_at)
                                ],
                            ],
                        ]) ?>
                </div>
            </div>
        </div>
    </div>
