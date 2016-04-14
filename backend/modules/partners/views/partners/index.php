<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 13.4.16
 * Time: 17.24
 */
use yii\helpers\Html;
use yii\grid\GridView;
use common\components\helpers\CustomHelper;
use common\models\CUserRequisites;
$this->title = Yii::t('app/users','Partners')
?>
<div class = "row">
    <div class = "col-md-12 col-sm-12 col-xs-12">
        <div class = "x_panel">
            <div class = "x_title">
                <h2><?php echo Html::encode($this->title)?></h2>
                <section class="pull-right">
                    <?php if(
                        Yii::$app->user->can('adminRights') ||
                        Yii::$app->user->can('only_bookkeeper') ||
                        Yii::$app->user->can('only_manager') ||
                        Yii::$app->user->can('only_jurist')
                    ):?>
                        <?php echo \yii\helpers\Html::a(Yii::t('app/crm','Add_new_company'),['create'],['class'=>'btn btn-primary']);?>
                    <?php endif;?>
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
                        'id',
                        [
                            'attribute' => 'corp_name',
                            'label' => Yii::t('app/users','Partner'),
                            'value' => function($model){
                                return $model->getInfoWithSite();
                            }
                        ],
                        [
                            'attribute' => 'fio',
                            'label' => Yii::t('app/users','Fio'),
                            'value' => function($model){
                                /** @var CUserRequisites $obR */
                                $obR = $model->requisites;
                                if(empty($obR))
                                    return 'N/A';
                                return CustomHelper::highlight('dummy',$obR->j_lname.' '.$obR->j_fname.' '.$obR->j_mname);
                            }
                        ],
                        [
                            'attribute' => 'phone',
                            'value' => 'requisites.c_phone'
                        ],
                        [
                            'attribute' => 'c_email',
                            'value' => 'requisites.c_email',
                        ],
                        [
                            'attribute' => 'manager_id',
                            'value' => 'manager.fio'
                        ],
                        [
                            'attribute' => 'partner_manager_id',
                            'value' => 'partnerManager.fio',
                        ],
                        [
                            'label' => '',
                            'format' => 'raw',
                            'value' => function(){
                                $arLinks = [
                                    [
                                        'title' => Yii::t('app/users','Partner link lids'),
                                        'href' => ['link-lid']
                                    ]
                                ];
                                return \common\components\helpers\CustomHtmlHelper::dropDownSettings($arLinks);
                            }
                        ],
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{view}{update}'
                        ],
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{delete}'
                        ]
                    ]
                    
                    //'columns' => $columns
                ]); ?>

            </div>
        </div>
    </div>
</div>


