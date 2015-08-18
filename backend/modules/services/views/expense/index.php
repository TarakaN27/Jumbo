<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\search\ExpenseCategoriesSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app/services', 'Expense Categories');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class = "row">

<div class = "col-md-12 col-sm-12 col-xs-12">
                            <div class = "x_panel">
                                <div class = "x_title">
                                    <h2><?php echo $this->title?></h2>
                                    <section class="pull-right">
                                     <?= Html::a(Yii::t('app/services', 'Create Expense Categories'), ['create'], ['class' => 'btn btn-success']) ?>
                                    </section>
                                    <div class = "clearfix"></div>
                                </div>
                                <div class = "x_content">
   <?php echo \common\components\widgets\WMCPageSize\WMCPageSize::widget();?>
    <?= GridView::widget([
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
                            return Html::a($model->name,['update','id'=>$model->id],['class'=>'link-upd']);
                    }
            ],
            'description',
            [
                'attribute' => 'parent_id',
                'value' => function($model){
                       $obParent = $model->parent;
                       return is_object($obParent) ? $obParent->name : NULL;
                    },
                'filter' => \common\models\ExpenseCategories::getParentCat()
            ],
            [
                'attribute' => 'status',
                'value' => function($model){
                        return $model->getStatusStr();
                    },
                'filter' => \common\models\ExpenseCategories::getStatusArr()
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
