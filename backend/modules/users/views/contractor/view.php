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
            //'id',
            'username',
            //'ext_id',
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
    <?php if(is_object($modelR)):

        switch ($modelR->type_id) {
            case \common\models\CUserRequisites::TYPE_J_PERSON:
                $dvConfig = [
                    'id',
                    [
                        'attribute' => 'type_id',
                        'value' => $modelR->getTypeStr()
                    ],
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
                    'j_address',
                    'p_address',
                ];
                if($model->is_resident == \common\models\CUser::RESIDENT_YES)
                {
                    $dvConfig = \yii\helpers\ArrayHelper::merge($dvConfig,['ynp','okpo']);
                }else{
                    $dvConfig = \yii\helpers\ArrayHelper::merge($dvConfig,['inn','kpp','ogrn']);
                }
                break;
            case \common\models\CUserRequisites::TYPE_I_PERSON:
                $dvConfig = [
                    'id',
                    [
                        'attribute' => 'type_id',
                        'value' => $modelR->getTypeStr()
                    ],
                    'j_fname',
                    'j_lname',
                    'j_mname',
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
                    'p_address',
                    'birthday',
                    'pasp_series',
                    'pasp_number',
                    'pasp_ident',
                    'pasp_auth',
                    'pasp_date'
                ];
                if($model->is_resident == \common\models\CUser::RESIDENT_YES)
                {
                    $dvConfig = \yii\helpers\ArrayHelper::merge($dvConfig,['ynp','okpo']);
                }else{
                    $dvConfig = \yii\helpers\ArrayHelper::merge($dvConfig,['inn','kpp','ogrn']);
                }
                break;
            case \common\models\CUserRequisites::TYPE_F_PERSON:
                $dvConfig = [
                    'id',
                    [
                        'attribute' => 'type_id',
                        'value' => $modelR->getTypeStr()
                    ],
                    'j_fname',
                    'j_lname',
                    'j_mname',
                    'c_fname',
                    'c_lname',
                    'c_mname',
                    'c_email',
                    'c_phone',
                    'c_fax',
                    'p_address',
                    'birthday',
                    'pasp_series',
                    'pasp_number',
                    'pasp_ident',
                    'pasp_auth',
                    'pasp_date'
                ];
                break;
            default:
                $dvConfig = [];

        }
        ?>
        <?= DetailView::widget([
            'model' => $modelR,
            'attributes' => $dvConfig
        ]) ?>

    <?php else:?>
        <?php echo Yii::t('app/users','Requisites not found')?>
    <?php endif;?>
                                </div>
                            </div>
                        </div>
</div>

