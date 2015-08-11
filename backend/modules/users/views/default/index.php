<?php

use yii\helpers\Html;
use yii\grid\GridView;
use backend\models\BUser;
/* @var $this yii\web\View */
/* @var $searchModel backend\models\search\BUserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app/users', 'Busers');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class = "row">
<div class = "col-md-12 col-sm-12 col-xs-12">
                            <div class = "x_panel">
                                <div class = "x_title">
                                    <h2><?php echo Html::encode($this->title);?></h2>
                                    <section class="pull-right">
                                    <?php if(Yii::$app->user->can('adminRights')):?>
                                        <?php echo \yii\helpers\Html::a(Yii::t('app/users','Add_new_user'),['/users/default/create'],['class'=>'btn btn-primary']);?>
                                        <?php echo \yii\helpers\Html::a(Yii::t('app/users','Add_invite'),['/users/default/add-invite'],['class'=>'btn btn-warning']);?>
                                    <?php endif;?>
                                    </section>
                                    <div class = "clearfix"></div>
                                </div>
                                <div class = "x_content">
                                    <?php echo\nterms\pagesize\PageSize::widget([
                                        'options' => [
                                            'class' => 'form-control input-sm',
                                        ],
                                        'label' => 'Отображать по:',
                                        'defaultPageSize' => Yii::$app->params['defaultPageSize'],
                                        'template' => '<div class="col-md-1 col-sm-1 col-xs-12 pull-right per-page-select ">{label}{list}</div>'
                                    ]);?>
                                    <?php
                                        $tpl = '';
                                        $viewTpl = '';
                                        if(Yii::$app->user->can('adminRights'))
                                        {
                                            $tpl = '{delete}';
                                            $viewTpl = '{view}';
                                        }
                                        elseif(Yii::$app->user->can('only_manager') || Yii::$app->user->can('only_bookkeeper'))
                                        {
                                            $viewTpl = '{view}';
                                        }
                                        echo GridView::widget([
                                            'dataProvider' => $dataProvider,
                                            'filterModel' => $searchModel,
                                            'filterSelector' => 'select[name="per-page"]',
                                            'columns' => [
                                                ['class' => 'yii\grid\SerialColumn'],
                                                [
                                                    'attribute' => 'username',
                                                    'format' => 'html',
                                                    'value' => function($model){
                                                            if(Yii::$app->user->can('adminRights'))
                                                                return Html::a(
                                                                    $model->username,
                                                                    ['update','id' => $model->id],
                                                                    ['class'=>'link-upd']);
                                                            else
                                                                return $model->username;
                                                        }
                                                ],
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
                                                    'filter' => \backend\models\BUser::getRoleByPermission()
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
                                                [
                                                    'class' => 'yii\grid\ActionColumn',
                                                    'template' => $viewTpl,
                                                ],
                                                [
                                                    'class' => 'yii\grid\ActionColumn',
                                                    'template' => $tpl,
                                                    'buttons' => [
                                                        'delete' => function ($url, $model, $key) {
                                                                $options = [
                                                                    'title' => Yii::t('yii', 'Delete'),
                                                                    'aria-label' => Yii::t('yii', 'Delete'),
                                                                    'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
                                                                    'data-method' => 'post',
                                                                    'data-pjax' => '0',
                                                                ];

                                                                if(Yii::$app->user->can('superRights'))
                                                                    return Html::a('<span class="glyphicon glyphicon-trash"></span>', $url, $options);
                                                                elseif(
                                                                    Yii::$app->user->can('adminRights') &&
                                                                    in_array($model->role,[
                                                                        BUser::ROLE_MANAGER,
                                                                        BUser::ROLE_BOOKKEEPER,
                                                                        BUser::ROLE_USER]))
                                                                return Html::a('<span class="glyphicon glyphicon-trash"></span>', $url, $options);
                                                                else
                                                                    return '';
                                                            }
                                                    ]

                                                ],
                                            ],
                                        ]); ?>

                                </div>
                            </div>
                        </div>
</div>

