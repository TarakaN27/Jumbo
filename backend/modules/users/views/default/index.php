<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\search\BUserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app/users', 'Busers');
$this->params['breadcrumbs'][] = $this->title;
?>



<div class = "page-title">
    <div class = "title_left">
         <h3><?php $this->title?> <small><?php echo Yii::t('app/users','USER_users_list');?></small></h3>
    </div>

    <div class = "title_right">

    </div>
</div>
<div class = "clearfix"></div>
<div class = "row">

<div class = "col-md-12 col-sm-12 col-xs-12">
                            <div class = "x_panel">
                                <div class = "x_title">
                                    <h2><?php echo Html::encode($this->title);?></h2>
                                    <section class="pull-right">
                                    <?php echo \yii\helpers\Html::a(Yii::t('app/users','Add_new_user'),['/users/default/create'],['class'=>'btn btn-primary']);?>
                                    <?php echo \yii\helpers\Html::a(Yii::t('app/users','Add_invite'),['/users/default/add-invite'],['class'=>'btn btn-warning']);?>
                                    </section>
                                    <div class = "clearfix"></div>
                                </div>
                                <div class = "x_content">
                                    <?= GridView::widget([
                                        'dataProvider' => $dataProvider,
                                        'filterModel' => $searchModel,
                                        'columns' => [
                                            ['class' => 'yii\grid\SerialColumn'],
                                            //'id',
                                            'username',
                                            [
                                                'attribute' => 'fio',
                                                'label' => Yii::t('app/users','Fio'),
                                                'value' => function($model){
                                                        return $model->getFio();
                                                    }
                                            ],
                                            'email:email',
                                            [
                                                'attribute' => 'role',
                                                'value' => function($model)
                                                    {
                                                        return $model->getRoleStr();
                                                    },
                                                'filter' => \backend\models\BUser::getRoleArr()
                                            ],
                                            [
                                                'attribute' => 'status',
                                                'value' => function($model){
                                                        return $model->getStatusStr();
                                                    },
                                                'filter' => \backend\models\BUser::getStatusArr()
                                            ],
                                            [
                                                'attribute' => 'created_at',
                                                'value' => function($model){
                                                        return $model->getFormatedCreatedAt();
                                                    }
                                            ],
                                            ['class' => 'yii\grid\ActionColumn'],
                                        ],
                                    ]); ?>

                                </div>
                            </div>
                        </div>
</div>

