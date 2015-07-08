<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\search\CUserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app/users', 'Cusers');
$this->params['breadcrumbs'][] = $this->title;
?>



<div class = "page-title">
    <div class = "title_left">
         <h3><?php $this->title?> <small><?php echo Yii::t('app/users','USER_contractors_list');?></small></h3>
    </div>

    <div class = "title_right">

    </div>
</div>
<div class = "clearfix"></div>
<div class = "row">

<div class = "col-md-12 col-sm-12 col-xs-12">
                            <div class = "x_panel">
                                <div class = "x_title">
                                    <h2><?php echo Html::encode($this->title)?></h2>
                                    <section class="pull-right">
                                    <?php echo \yii\helpers\Html::a(Yii::t('app/users','Add_new_contractor'),['create'],['class'=>'btn btn-primary']);?>
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
                                            'username',
                                            'ext_id',
                                            [
                                                'attribute' => 'type',
                                                'value' => function($model){
                                                        return $model->getTypeStr();
                                                    },
                                                'filter' => \common\models\CUser::getTypeArr()
                                            ],
                                            [
                                                'attribute' => 'manager_id',
                                                'value' => function($model){
                                                        $manager = $model->manager;
                                                        return is_object($manager) ? $manager->username : NULL;
                                                    },
                                                'filter' => \backend\models\BUser::getListManagers()

                                            ],
                                            'email:email',
                                            // 'role',
                                            [
                                                'attribute' => 'status',
                                                'value' => function($model){
                                                        return $model->getStatusStr();
                                                    },
                                                'filter' => \common\models\CUser::getStatusArr()
                                            ],
                                            // 'created_at',
                                            // 'updated_at',

                                            ['class' => 'yii\grid\ActionColumn'],
                                        ],
                                    ]); ?>

                                </div>
                            </div>
                        </div>
</div>
