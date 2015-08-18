<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\search\UnitsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app/units', 'Units');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class = "row">
    <div class = "col-md-12 col-sm-12 col-xs-12">
        <div class = "x_panel">
            <div class = "x_title">
                <h2><?php echo $this->title?></h2>
                <section class="pull-right">
                    <?= Html::a(Yii::t('app/units', 'Create Units'), ['create'], ['class' => 'btn btn-success']) ?>
                </section>
                <div class = "clearfix"></div>
            </div>
            <div class = "x_content">
               <?php echo \common\components\widgets\WMCPageSize\WMCPageSize::widget();?>
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'filterSelector' => 'select[name="per-page"]',
                    'rowOptions' => [
                        'class' => 'ola'
                    ],
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],
                        [
                            'attribute' => 'name',
                            'format' => 'html',
                            'value' => function($model)
                                {
                                    return Html::a($model->name,['edit'],['class' => 'link-upd']);
                                }
                        ],
                        'cost',
                        [
                            'attribute' => 'service_id',
                            'value' => function($model){
                                    return is_object($obServ = $model->service) ? $obServ->name : 'N/A';
                                },
                            'filter' => \common\models\Services::getServicesMap()
                        ],
                        [
                            'attribute' => 'cuser_id',
                            'value' => function($model){
                                    return is_object($obCUser = $model->cuser) ? $obCUser->getInfo() : $model->cuser_id;
                                },
                            'filter' => \common\models\CUser::getContractorMap(),
                        ],
                        [
                            'attribute' => 'type',
                            'value' => function($model){
                                    return $model->getTypeStr();
                                },
                            'filter' => \app\models\Units::getTypeArr()
                        ],
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{view}'
                        ],
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{delete}'
                        ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>
</div>

