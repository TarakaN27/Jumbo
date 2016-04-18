<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 14.4.16
 * Time: 15.11
 */
use yii\helpers\Html;
use yii\grid\GridView;
$this->registerCssFile('@web/css/craftpip-jquery-confirm/css/jquery-confirm.css');
$this->registerJsFile('@web/js/datepicker/daterangepicker.js',['depends' => ['yii\web\YiiAsset', 'yii\bootstrap\BootstrapAsset'],]);
$this->registerJsFile('@web/js/moment.min2.js',['depends' => ['yii\web\YiiAsset', 'yii\bootstrap\BootstrapAsset'],]);
$this->registerJsFile('@web/js/craftpip-jquery-confirm/js/jquery-confirm.js',['depends' => ['yii\web\YiiAsset', 'yii\bootstrap\BootstrapAsset'],]);
$this->registerJsFile('@web/js/parts/partner_link_grid.js',['depends' => ['yii\web\YiiAsset', 'yii\bootstrap\BootstrapAsset'],]);
$this->registerJs("
var
    URL_ARCHIVE_LINK = '".\yii\helpers\Url::to(['archive'])."';

",\yii\web\View::POS_HEAD);
$this->title = Yii::t('app/users','Partner link lead');

?>
<div class = "row">
    <div class = "col-md-12 col-sm-12 col-xs-12">
        <div class = "x_panel">
            <div class = "x_title">
                <h2><?php echo Html::encode($this->title)?></h2>
                <section class="pull-right">
                    <?= Html::a(Yii::t('app/services', 'To list'), ['index'], ['class' => 'btn btn-warning']) ?>
                    <?=Html::a(Yii::t('app/users','Add link'),
                        ['add-link','pid' => $pid],
                        [
                            'class' => 'btn btn-success'
                        ]
                    )?>
                </section>
                <div class = "clearfix"></div>
            </div>
            <div class = "x_content">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'columns' => [
                        'id',
                        [
                            'attribute' => 'cuser_id',
                            'value' => 'cuser.infoWithSite',
                        ],
                        [
                            'attribute' => 'service_id',
                            'value' => 'service.name'
                        ],
                        'connect:date',
                        'created_at:datetime',
                        [
                            'attribute' => 'archive',
                            'format' => 'raw',
                            'value' => function($model){
                                $str = '<i class="fa fa-archive"></i>';
                                $options = [
                                    'class' => 'archive-btn',
                                    'data-pk' => $model->id,
                                    'data-value' => $model->archive,
                                    'data-toggle' => 'tooltip',
                                    'data-placement' => 'top',
                                    'data-original-title' => $model->archive ? Yii::t('app/users','Restore partner lead link') : Yii::t('app/users','Archive partner lead link'),
                                ];
                                if($model->archive)
                                    $options['class']  = 'archive-btn red';

                               return Html::a($str,NULL,$options);
                            }
                        ],
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{delete}',
                        ]
                    ]
                ]); ?>
            </div>
        </div>
    </div>
</div>