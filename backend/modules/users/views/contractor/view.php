<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\CUser */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/users', 'Cusers'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class = "clearfix"></div>
<div class = "row">

<div class = "col-md-12 col-sm-12 col-xs-12">
                            <div class = "x_panel">
                                <div class = "x_title">
                                    <h2>Контрагенты</h2>
                                    <section class="pull-right">

                                    <?= Html::a(Yii::t('app/users', 'To list'), ['index'], ['class' => 'btn btn-warning']) ?>
                                    <?php if(is_object($modelR)):?>
                                        <?= Html::a(Yii::t('app/users', 'Edit requisites'), ['edit-requisites','id'=>$modelR->id,'userID' => $model->id], ['class' => 'btn btn-primary']) ?>
                                    <?php else:?>
                                        <?= Html::a(Yii::t('app/users', 'Add requisites'), ['add-requisites','userID' => $model->id], ['class' => 'btn btn-primary']) ?>
                                    <?php endif;?>
                                    <?= Html::a(Yii::t('app/users','Add_new_contractor'),['create'],['class'=>'btn btn-primary']);?>
                                    <?= Html::a(Yii::t('app/users', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                                    <?= Html::a(Yii::t('app/users', 'Delete'), ['delete', 'id' => $model->id], [
                                        'class' => 'btn btn-danger',
                                        'data' => [
                                            'confirm' => Yii::t('app/users', 'Are you sure you want to delete this item?'),
                                            'method' => 'post',
                                        ],
                                    ]) ?>

                                    </section>
                                    <div class = "clearfix"></div>
                                </div>
                                <div class = "x_content">
    <?php  $obMng = $model->manager;?>
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'username',
            'ext_id',
            [
                'attribute' => 'type',
                'value' => is_object($obType = $model->userType) ? $obType->name : 'N/A'
            ],
            [
                'attribute' => 'manager_id',
                'value' => is_object($obMng) ? $obMng->username : NULL
            ],
            /*
            'auth_key',
            'password_hash',
            'password_reset_token',
            */
            'email:email',
            [
                'attribute' => 'is_resident',
                'value' => $model->getIsResidentStr()
            ],
            'r_country',
            [
                'attribute' => 'status',
                'value' => $model->getStatusStr()
            ],
            [
                'attribute' => 'created_at',
                'value' => $model->getFormatedCreatedAt()
            ],
            [
                'attribute' => 'updated_at',
                'value' => $model->getFormatedUpdatedAt()
            ]
        ],
    ]) ?>


    <h4><?php echo Yii::t('app/users','Requisites')?></h4>
    <div class="ln_solid"></div>
    <?php if(is_object($modelR)):?>
        <?= DetailView::widget([
            'model' => $modelR,
            'attributes' => [
                'id',
                'corp_name',
                'j_fname',
                'j_lname',
                'j_mname',
                'j_post',
                'j_doc',
                'reg_number',
                'reg_auth',
                'ch_account',
                'b_name',
                'b_code',
                'c_fname',
                'c_lname',
                'c_mname',
                'c_email',
                'c_phone',
                'c_fax',
                'ynp',
                'okpo',
                'inn',
                'kpp',
                'ogrn',
                'j_address',
                'p_address'
            ],
        ]) ?>

    <?php else:?>
        <?php echo Yii::t('app/users','Requisites not found')?>
    <?php endif;?>
                                </div>
                            </div>
                        </div>
</div>

