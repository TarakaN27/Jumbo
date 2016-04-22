<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 21.4.16
 * Time: 10.35
 */
use yii\helpers\Html;
$this->title = Yii::t('app/users',Yii::t('app/users','Partner detail'))
?>
<div class = "row">
    <div class = "col-md-12 col-sm-12 col-xs-12">
        <div class = "x_panel">
            <div class = "x_title">
                <h2><?php echo Html::encode($this->title)?></h2>
                <section class="pull-right">
                    <?= Html::a(Yii::t('app/users', 'To list'), ['index'], ['class' => 'btn btn-warning']) ?>
                    <?= Html::a(Yii::t('app/users', 'View detail lead statistic'), ['view-lead-detail','pid' => $pid], ['class' => 'btn btn-info']) ?>
                </section>
                <div class = "clearfix"></div>
            </div>
            <div class = "x_content">
                <?php echo \yii\widgets\DetailView::widget([
                    'model' => $obPartner,
                    'attributes' => [
                        'info',
                        [
                            'attribute' => 'userType.name',
                            'label' => Yii::t('app/users','Type'),
                        ],
                        [
                            'attribute' => 'prospects.name',
                            'label' => Yii::t('app/users','Prospects'),
                        ],
                        [
                            'attribute' => 'manager_id',
                            'value' => is_object($obMan = $obPartner->manager) ? $obMan->getFio() : NULL
                        ],
                        [
                            'attribute' => 'partner_manager_id',
                            'value' => is_object($obPMan = $obPartner->partnerManager) ? $obPMan->getFio() : NULL
                        ],
                        [
                            'attribute' => 'partner_scheme',
                            'value' => is_object($obPSch = $obPartner->partnerScheme) ? $obPSch->name : NULL
                        ],
                        'contractor:boolean',
                        'allow_expense:boolean',
                        'is_resident:boolean',
                        'r_country',
                    ]
                ])?>
                <?=Html::tag('h4',Yii::t('app/users','Partner purse'))?>
                <?php
                    if(is_object($obPurse))
                        echo \yii\widgets\DetailView::widget([
                            'model' => $obPurse,
                            'attributes' => [
                                'id',
                                'amount:decimal',
                                'withdrawal:decimal',
                                'availableAmount:decimal',
                                /*
                                [
                                    'label' => Yii::t('app/users','Amount available partner purse'),
                                    'value' => Yii::$app->formatter->as
                                ],
                                */
                                'created_at:datetime',
                                'updated_at:datetime'
                            ]
                        ]);
                    else
                        echo Yii::t('app/users','Partner purse not exists.')
                    ?>


                <?=Html::tag('h4',Yii::t('app/users','Partner leads'))?>
                <?php echo \yii\grid\GridView::widget([
                    'dataProvider' => $arLeadsProvider,
                    'columns' => [
                        'id',
                        [
                            'attribute' => 'cuser_id',
                            'value' => 'cuser.infoWithSite'
                        ],
                        [
                            'attribute' => 'service_id',
                            'value' => 'service.name'
                        ],
                        'connect:date',
                        'archive:boolean',
                        'st_period_checked:boolean',
                        'created_at:datetime'
                    ]
                ])?>


            </div>
        </div>
    </div>
</div>

