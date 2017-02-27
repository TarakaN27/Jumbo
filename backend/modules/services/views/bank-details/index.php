<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\search\LegalPersonSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app/services', 'Bank Details');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class = "row">
    <div class = "col-md-12 col-sm-12 col-xs-12">
        <div class = "x_panel">
            <div class = "x_title">
                <h2><?php echo $this->title?></h2>
                <section class="pull-right">
                <?php if(Yii::$app->user->can('superRights')):?>
                    <?= Html::a(Yii::t('app/services', 'Create Bank Details'), ['create'], ['class' => 'btn btn-success']) ?>
                <?php endif;?>
                </section>
                <div class = "clearfix"></div>
            </div>
            <div class = "x_content">
                <?php echo \common\components\widgets\WMCPageSize\WMCPageSize::widget();?>
                <?php

                    $tpl = '';
                    $viewTpl = '';
                    if(Yii::$app->user->can('superRights'))
                    {
                        $tpl = '{delete}';
                        $viewTpl = '{view}';
                    }
                    elseif(Yii::$app->user->can('adminRights'))
                        $viewTpl = '{view}';

                    echo GridView::widget([
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
                        'filterSelector' => 'select[name="per-page"]',
                        'columns' => [
                            ['class' => 'yii\grid\SerialColumn'],
                            [
                                'attribute' => 'name',
                                'format' => 'html',
                                'value' => function($model)
                                    {
                                        if(Yii::$app->user->can('adminRights'))
                                            return Html::a($model->name,['update','id'=>$model->id],['class'=>'link-upd']);
                                        else
                                            return $model->name;
                                    }
                            ],
                            [
                                'attribute'=>'legal_person_id',
                                'value'=>'legalPerson.name',
                                'filter' => \common\models\LegalPerson::getLegalPersonMap()
                            ],
                            'bank_details:ntext',
                            [
                                'attribute' => 'status',
                                'value' => function($model){
                                        return $model->getStatusStr();
                                    },
                                'filter' => \common\models\LegalPerson::getStatusArr()
                            ],
                            'created_at:datetime',
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'template' => $viewTpl
                            ],
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'template' => $tpl
                            ],
                        ],
                    ]); ?>

            </div>
        </div>
    </div>
</div>
