<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use common\components\helpers\CustomHelper;
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
            'id',
            'username',
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
                    //'id',
                    [
                        'attribute' => 'type_id',
                        'value' => $modelR->getTypeStr()
                    ],
                    [
                        'attribute' => 'corp_name',
                        'format' => 'html',
                        'value' => CustomHelper::highlight('dummy',$modelR->corp_name)
                    ],

                    [
                        'attribute' => 'j_fname',
                        'format' => 'html',
                        'value' => CustomHelper::highlight('dummy',$modelR->j_fname)
                    ],
                    [
                        'attribute' => 'j_lname',
                        'format' => 'html',
                        'value' => CustomHelper::highlight('dummy',$modelR->j_lname)
                    ],
                    [
                        'attribute' => 'j_mname',
                        'format' => 'html',
                        'value' => CustomHelper::highlight('dummy',$modelR->j_mname)
                    ],
                    [
                        'attribute' => 'j_post',
                        'format' => 'html',
                        'value' => CustomHelper::highlight('dummy',$modelR->j_post)
                    ],
                    [
                        'attribute' => 'j_doc',
                        'format' => 'html',
                        'value' => CustomHelper::highlight('dummy',$modelR->j_doc)
                    ],
                    [
                        'attribute' => 'reg_number',
                        'format' => 'html',
                        'value' => CustomHelper::highlight('dummy',$modelR->reg_number)
                    ],
                    [
                        'attribute' => 'reg_auth',
                        'format' => 'html',
                        'value' => CustomHelper::highlight('dummy',$modelR->reg_auth)
                    ],
                    [
                        'attribute' => 'ch_account',
                        'format' => 'html',
                        'value' => CustomHelper::highlight('dummy',$modelR->ch_account)
                    ],
                    [
                        'attribute' => 'b_name',
                        'format' => 'html',
                        'value' => CustomHelper::highlight('dummy',$modelR->b_name)
                    ],
                    [
                        'attribute' => 'b_code',
                        'format' => 'html',
                        'value' => CustomHelper::highlight('dummy',$modelR->b_code)
                    ],
                    [
                        'attribute' => 'c_fname',
                        'format' => 'html',
                        'value' => CustomHelper::highlight('dummy',$modelR->c_fname)
                    ],

                    [
                        'attribute' => 'c_lname',
                        'format' => 'html',
                        'value' => CustomHelper::highlight('dummy',$modelR->c_lname)
                    ],
                    [
                        'attribute' => 'c_mname',
                        'format' => 'html',
                        'value' => CustomHelper::highlight('dummy',$modelR->c_mname)
                    ],
                    [
                        'attribute' => 'c_email',
                        'format' => 'html',
                        'value' => CustomHelper::highlight('dummy',$modelR->c_email)
                    ],
                    [
                        'attribute' => 'c_phone',
                        'format' => 'html',
                        'value' => CustomHelper::highlight('dummy',$modelR->c_phone)
                    ],
                    [
                        'attribute' => 'c_fax',
                        'format' => 'html',
                        'value' => CustomHelper::highlight('dummy',$modelR->c_fax)
                    ],
                    [
                        'attribute' => 'j_address',
                        'format' => 'html',
                        'value' => CustomHelper::highlight('dummy',$modelR->j_address)
                    ],
                    [
                        'attribute' => 'p_address',
                        'format' => 'html',
                        'value' => CustomHelper::highlight('dummy',$modelR->p_address)
                    ],
                ];
                if($model->is_resident == \common\models\CUser::RESIDENT_YES)
                {
                    $dvConfig = \yii\helpers\ArrayHelper::merge($dvConfig,[

                        [
                            'attribute' => 'ynp',
                            'format' => 'html',
                            'value' => CustomHelper::highlight('dummy',$modelR->ynp)
                        ],
                        [
                            'attribute' => 'okpo',
                            'format' => 'html',
                            'value' => CustomHelper::highlight('dummy',$modelR->okpo)
                        ],

                    ]);
                }else{
                    $dvConfig = \yii\helpers\ArrayHelper::merge($dvConfig,[
                        [
                            'attribute' => 'inn',
                            'format' => 'html',
                            'value' => CustomHelper::highlight('dummy',$modelR->inn)
                        ],
                        [
                            'attribute' => 'kpp',
                            'format' => 'html',
                            'value' => CustomHelper::highlight('dummy',$modelR->kpp)
                        ],
                        [
                            'attribute' => 'ogrn',
                            'format' => 'html',
                            'value' => CustomHelper::highlight('dummy',$modelR->ogrn)
                        ],
                    ]);
                }
                break;
            case \common\models\CUserRequisites::TYPE_I_PERSON:
                $dvConfig = [
                    [
                        'attribute' => 'type_id',
                        'value' => $modelR->getTypeStr()
                    ],
                    [
                        'attribute' => 'j_fname',
                        'format' => 'html',
                        'value' => CustomHelper::highlight('dummy',$modelR->j_fname)
                    ],
                    [
                        'attribute' => 'j_lname',
                        'format' => 'html',
                        'value' => CustomHelper::highlight('dummy',$modelR->j_lname)
                    ],
                    [
                        'attribute' => 'j_mname',
                        'format' => 'html',
                        'value' => CustomHelper::highlight('dummy',$modelR->j_mname)
                    ],

                    [
                        'attribute' => 'reg_number',
                        'format' => 'html',
                        'value' => CustomHelper::highlight('dummy',$modelR->reg_number)
                    ],
                    [
                        'attribute' => 'reg_auth',
                        'format' => 'html',
                        'value' => CustomHelper::highlight('dummy',$modelR->reg_auth)
                    ],
                    [
                        'attribute' => 'ch_account',
                        'format' => 'html',
                        'value' => CustomHelper::highlight('dummy',$modelR->ch_account)
                    ],

                    [
                        'attribute' => 'b_name',
                        'format' => 'html',
                        'value' => CustomHelper::highlight('dummy',$modelR->b_name)
                    ],
                    [
                        'attribute' => 'b_code',
                        'format' => 'html',
                        'value' => CustomHelper::highlight('dummy',$modelR->b_code)
                    ],
                    [
                        'attribute' => 'c_fname',
                        'format' => 'html',
                        'value' => CustomHelper::highlight('dummy',$modelR->c_fname)
                    ],

                    [
                        'attribute' => 'c_lname',
                        'format' => 'html',
                        'value' => CustomHelper::highlight('dummy',$modelR->c_lname)
                    ],
                    [
                        'attribute' => 'c_mname',
                        'format' => 'html',
                        'value' => CustomHelper::highlight('dummy',$modelR->c_mname)
                    ],
                    [
                        'attribute' => 'c_email',
                        'format' => 'html',
                        'value' => CustomHelper::highlight('dummy',$modelR->c_email)
                    ],

                    [
                        'attribute' => 'c_phone',
                        'format' => 'html',
                        'value' => CustomHelper::highlight('dummy',$modelR->c_phone)
                    ],
                    [
                        'attribute' => 'c_fax',
                        'format' => 'html',
                        'value' => CustomHelper::highlight('dummy',$modelR->c_fax)
                    ],
                    [
                        'attribute' => 'p_address',
                        'format' => 'html',
                        'value' => CustomHelper::highlight('dummy',$modelR->p_address)
                    ],

                    [
                        'attribute' => 'birthday',
                        'format' => 'html',
                        'value' => CustomHelper::highlight('dummy',$modelR->birthday)
                    ],
                    [
                        'attribute' => 'pasp_series',
                        'format' => 'html',
                        'value' => CustomHelper::highlight('dummy',$modelR->pasp_series)
                    ],
                    [
                        'attribute' => 'pasp_number',
                        'format' => 'html',
                        'value' => CustomHelper::highlight('dummy',$modelR->pasp_number)
                    ],

                    [
                        'attribute' => 'pasp_ident',
                        'format' => 'html',
                        'value' => CustomHelper::highlight('dummy',$modelR->pasp_ident)
                    ],
                    [
                        'attribute' => 'pasp_auth',
                        'format' => 'html',
                        'value' => CustomHelper::highlight('dummy',$modelR->pasp_auth)
                    ],
                    [
                        'attribute' => 'pasp_date',
                        'format' => 'html',
                        'value' => CustomHelper::highlight('dummy',$modelR->pasp_date)
                    ],
                ];
                if($model->is_resident == \common\models\CUser::RESIDENT_YES)
                {
                    $dvConfig = \yii\helpers\ArrayHelper::merge($dvConfig,[
                        [
                            'attribute' => 'ynp',
                            'format' => 'html',
                            'value' => CustomHelper::highlight('dummy',$modelR->ynp)
                        ],
                        [
                            'attribute' => 'okpo',
                            'format' => 'html',
                            'value' => CustomHelper::highlight('dummy',$modelR->okpo)
                        ],
                    ]);
                }else{
                    $dvConfig = \yii\helpers\ArrayHelper::merge($dvConfig,[
                        [
                            'attribute' => 'inn',
                            'format' => 'html',
                            'value' => CustomHelper::highlight('dummy',$modelR->inn)
                        ],
                        [
                            'attribute' => 'kpp',
                            'format' => 'html',
                            'value' => CustomHelper::highlight('dummy',$modelR->kpp)
                        ],
                        [
                            'attribute' => 'ogrn',
                            'format' => 'html',
                            'value' => CustomHelper::highlight('dummy',$modelR->ogrn)
                        ],
                    ]);
                }
                break;
            case \common\models\CUserRequisites::TYPE_F_PERSON:
                $dvConfig = [
                    //'id',
                    [
                        'attribute' => 'type_id',
                        'value' => $modelR->getTypeStr()
                    ],
                    [
                        'attribute' => 'j_fname',
                        'format' => 'html',
                        'value' => CustomHelper::highlight('dummy',$modelR->j_fname)
                    ],
                    [
                        'attribute' => 'j_lname',
                        'format' => 'html',
                        'value' => CustomHelper::highlight('dummy',$modelR->j_lname)
                    ],
                    [
                        'attribute' => 'j_mname',
                        'format' => 'html',
                        'value' => CustomHelper::highlight('dummy',$modelR->j_mname)
                    ],
                    [
                        'attribute' => 'c_fname',
                        'format' => 'html',
                        'value' => CustomHelper::highlight('dummy',$modelR->c_fname)
                    ],

                    [
                        'attribute' => 'c_lname',
                        'format' => 'html',
                        'value' => CustomHelper::highlight('dummy',$modelR->c_lname)
                    ],
                    [
                        'attribute' => 'c_mname',
                        'format' => 'html',
                        'value' => CustomHelper::highlight('dummy',$modelR->c_mname)
                    ],
                    [
                        'attribute' => 'c_email',
                        'format' => 'html',
                        'value' => CustomHelper::highlight('dummy',$modelR->c_email)
                    ],

                    [
                        'attribute' => 'c_phone',
                        'format' => 'html',
                        'value' => CustomHelper::highlight('dummy',$modelR->c_phone)
                    ],
                    [
                        'attribute' => 'c_fax',
                        'format' => 'html',
                        'value' => CustomHelper::highlight('dummy',$modelR->c_fax)
                    ],
                    [
                        'attribute' => 'p_address',
                        'format' => 'html',
                        'value' => CustomHelper::highlight('dummy',$modelR->p_address)
                    ],

                    [
                        'attribute' => 'birthday',
                        'format' => 'html',
                        'value' => CustomHelper::highlight('dummy',$modelR->birthday)
                    ],
                    [
                        'attribute' => 'pasp_series',
                        'format' => 'html',
                        'value' => CustomHelper::highlight('dummy',$modelR->pasp_series)
                    ],
                    [
                        'attribute' => 'pasp_number',
                        'format' => 'html',
                        'value' => CustomHelper::highlight('dummy',$modelR->pasp_number)
                    ],


                    [
                        'attribute' => 'pasp_ident',
                        'format' => 'html',
                        'value' => CustomHelper::highlight('dummy',$modelR->pasp_ident)
                    ],
                    [
                        'attribute' => 'pasp_auth',
                        'format' => 'html',
                        'value' => CustomHelper::highlight('dummy',$modelR->pasp_auth)
                    ],
                    [
                        'attribute' => 'pasp_date',
                        'format' => 'html',
                        'value' => CustomHelper::highlight('dummy',$modelR->pasp_date)
                    ],
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

