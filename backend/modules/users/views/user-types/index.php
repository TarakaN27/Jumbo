<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\search\CUserTypesSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app/users', 'Cuser Types');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class = "page-title">
    <div class = "title_left">
         <h3><?php echo $this->title?></h3>
    </div>
</div>
<div class = "clearfix"></div>
<div class = "row">

<div class = "col-md-12 col-sm-12 col-xs-12">
                            <div class = "x_panel">
                                <div class = "x_title">
                                    <h2><?php echo $this->title?></h2>
                                    <section class="pull-right">
                                     <?= Html::a(Yii::t('app/users', 'Create Cuser Types'), ['create'], ['class' => 'btn btn-success']) ?>
                                    </section>
                                    <div class = "clearfix"></div>
                                </div>
                                <div class = "x_content">
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'name',
            'description:ntext',
            [
                'attribute' => 'created_at',
                'value' => function($model){
                        return $model->getFormatedCreatedAt();
                    }
            ],
            [
                'attribute' => 'updated_at',
                'value' => function($model){
                        return $model->getFormatedUpdatedAt();
                    }
            ],
            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

                                </div>
                            </div>
                        </div>
</div>
