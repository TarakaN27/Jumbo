<?php

use yii\helpers\Html;
use yii\grid\GridView;
use common\models\CUserRequisites;
use common\components\helpers\CustomHelper;
/* @var $this yii\web\View */
/* @var $searchModel common\models\search\CUserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app/users', 'Cusers');
$this->params['breadcrumbs'][] = $this->title;
?>

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

                                    <?php echo \common\components\widgets\WMCPageSize\WMCPageSize::widget();?>
                                    <?= GridView::widget([
                                        'dataProvider' => $dataProvider,
                                        'filterModel' => $searchModel,
                                        'filterSelector' => 'select[name="per-page"]',
                                        'columns' => [
                                            ['class' => 'yii\grid\SerialColumn'],
                                            [
                                                'attribute' => 'corp_name',
                                                'label' => Yii::t('app/users', 'Corp Name'),
                                                'format' => 'html',
                                                'value' => function($model){
                                                        /** @var CUserRequisites $obR */
                                                        $obR = $model->requisites;
                                                        if(empty($obR))
                                                            return Html::a('N/A',['update','id' => $model->id],['class'=>'link-upd']);
                                                        return Html::a(
                                                            CustomHelper::highlight('dummy',$obR->getCorpName()),
                                                            ['update','id' => $model->id],
                                                            ['class'=>'link-upd']);
                                                    }
                                            ],
                                            [
                                                'attribute' => 'fio',
                                                'label' => Yii::t('app/users','FIO'),
                                                'format' => 'html',
                                                'value' => function($model){
                                                        /** @var CUserRequisites $obR */
                                                        $obR = $model->requisites;
                                                        if(empty($obR))
                                                            return 'N/A';
                                                        return CustomHelper::highlight('dummy',$obR->j_lname.' '.$obR->j_fname.' '.$obR->j_mname);
                                                    }
                                            ],
                                            [
                                                'attribute' => 'type',
                                                'format' => 'html',
                                                'value' => function($model){
                                                        return is_object($obType = $model->userType) ? CustomHelper::highlight('dummy',$obType->name) : 'N/A';
                                                    },
                                                'filter' => \common\models\CUserTypes::getUserTypesMap()
                                            ],
                                            [
                                                'attribute' => 'manager_id',
                                                'value' => function($model){
                                                        $manager = $model->manager;
                                                        return is_object($manager) ? $manager->username : NULL;
                                                    },
                                                'filter' => \backend\models\BUser::getListManagers()
                                            ],
                                            [
                                                'attribute' => 'status',
                                                'value' => function($model){
                                                        return $model->getStatusStr();
                                                    },
                                                'filter' => \common\models\CUser::getStatusArr()
                                            ],

                                            [
                                                'label' => '',
                                                'format' => 'raw',
                                                'value' => function($model){
                                                        return '
                                                            <div class="btn-group">
                                                <button data-toggle="dropdown" class="btn btn-default dropdown-toggle" type="button" aria-expanded="false">
                                                    '.Yii::t('app/book','Settings').' <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        '.Html::a(Yii::t('app/users','Prefer condition'),['prefer-cond','id' => $model->id]).'
                                                    </li>
                                                    <li>
                                                        '.Html::a(Yii::t('app/users','External account'),['external-account','iCID' => $model->id]).'
                                                    </li>
                                                </ul>
                                            </div>
                                                            ';
                                                    }
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
