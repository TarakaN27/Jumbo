<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use backend\models\BUser;
/* @var $this yii\web\View */
/* @var $model backend\models\BUser */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/users', 'Busers'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class = "clearfix"></div>
<div class = "row">

<div class = "col-md-12 col-sm-12 col-xs-12">
                            <div class = "x_panel">
                                <div class = "x_title">
                                    <h2>Сотрудники</h2>
                                    <section class="pull-right">
                                    <?= Html::a(Yii::t('app/users', 'To users list'), ['index', 'id' => $model->id], ['class' => 'btn btn-warning']) ?>

                                    <?php if(Yii::$app->user->can('superRights')):?>
                                        <?= HTML::a(Yii::t('app/users','Bind_members'),
                                            ['/users/default/bind-members','id' => $model->id],
                                            ['class' => 'btn btn-primary']
                                        )?>
                                    <?php endif;?>
                                    <?php if(Yii::$app->user->can('adminRights')):?>
                                        <?= Html::a(Yii::t('app/users','Add_new_user'),['/users/default/create'],['class'=>'btn btn-primary']);?>
                                        <?= Html::a(Yii::t('app/users', 'Change_password'), ['change-password', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                                        <?= Html::a(Yii::t('app/users', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                                    <?php endif;?>
                                    <?php if((Yii::$app->user->can('adminRights') &&
                                            in_array($model->role,[BUser::ROLE_MANAGER,BUser::ROLE_BOOKKEEPER,BUser::ROLE_USER])) ||
                                        Yii::$app->user->can('superRights')
                                    ): ?>
                                    <?= Html::a(Yii::t('app/users', 'Delete'), ['delete', 'id' => $model->id], [
                                        'class' => 'btn btn-danger',
                                        'data' => [
                                            'confirm' => Yii::t('app/users', 'Are you sure you want to delete this item?'),
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
            'username',
            [
                'label' => Yii::t('app/users','Fio'),
                'value' => $model->getFio()
            ],
            'auth_key',
            'password_hash',
            'password_reset_token',
            'email:email',
            [
                'attribute' => 'role',
                'value' => $model->getRoleStr()
            ],
            [
                'attribute' => 'crm_group_id',
                'value' => is_object($obGroup = $model->cRMGroup) ? $obGroup->name : $model->crm_group_id
            ],
            [
                'attribute' => 'log_work_type',
                'value' => $model->getLogWorkTypeStr()
            ],
            'allow_unit:boolean',
            'allow_set_sale:boolean',
            [
                'attribute' => 'status',
                'value' => $model->getStatusStr()
            ],
            'created_at:datetime',
            'updated_at:datetime',
        ],
    ]) ?>

                                </div>
                            </div>
                        </div>
</div>

