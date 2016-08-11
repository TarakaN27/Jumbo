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
                                'totalIncomingAmount:decimal',
                                'totalExpenseAmount:decimal',
                                'availableAmount:decimal',
                                'pendingAmount:decimal',
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
                <?php
                $prevId=0;
                foreach($arLeads as $model){
                ?>
                <?php if ($prevId == 0){ ?>
                <div class="panel-group">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4 class="panel-title">
                                <a data-toggle="collapse" href="#collapse<?=$model->cuser_id?>"><?= $model->cuser->infoWithSite ?></a>
                            </h4>
                        </div>
                        <div id="collapse<?=$model->cuser_id?>" class="panel-collapse collapse">
                            <div class="panel-body">
                                <table class="table table-striped table-bordered">
                                    <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Услуга</th>
                                        <th>Дата подключения</th>
                                        <th>Архивная</th>
                                        <th>Стартовый период пройден</th>
                                        <th>Создано</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td><?= $model->id ?></td>
                                        <td><?= $model->service->name ?></td>
                                        <td><?= Yii::$app->formatter->asDate($model->connect) ?></td>
                                        <td><?= Yii::$app->formatter->asBoolean($model->archive) ?>Нет</td>
                                        <td><?= Yii::$app->formatter->asBoolean($model->st_period_checked) ?></td>
                                        <td><?= Yii::$app->formatter->asDatetime($model->created_at) ?></td>
                                    </tr>
                                    <?
                                    } elseif ($model->cuser_id == $prevId) { ?>
                                        <tr>
                                            <td><?= $model->id ?></td>
                                            <td><?= $model->service->name ?></td>
                                            <td><?= Yii::$app->formatter->asDate($model->connect) ?></td>
                                            <td><?= Yii::$app->formatter->asBoolean($model->archive) ?></td>
                                            <td><?= Yii::$app->formatter->asBoolean($model->st_period_checked) ?></td>
                                            <td><?= Yii::$app->formatter->asDatetime($model->created_at) ?></td>
                                        </tr>
                                    <?php }
                                    else{ ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="panel-group">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4 class="panel-title">
                                <a data-toggle="collapse" href="#collapse<?=$model->cuser_id?>"><?= $model->cuser->infoWithSite ?></a>
                            </h4>
                        </div>
                        <div id="collapse<?=$model->cuser_id?>" class="panel-collapse collapse">
                            <div class="panel-body">
                                <table class="table table-striped table-bordered">
                                    <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Услуга</th>
                                        <th>Дата подключения</th>
                                        <th>Архивная</th>
                                        <th>Стартовый период пройден</th>
                                        <th>Создано</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td><?= $model->id ?></td>
                                        <td><?= $model->service->name ?></td>
                                        <td><?= Yii::$app->formatter->asDate($model->connect) ?></td>
                                        <td><?= Yii::$app->formatter->asBoolean($model->archive) ?></td>
                                        <td><?= Yii::$app->formatter->asBoolean($model->st_period_checked) ?></td>
                                        <td><?= Yii::$app->formatter->asDatetime($model->created_at) ?></td>
                                    </tr>

                                    <?
                                    }
                                    $prevId=$model->cuser_id;
                                    }

                ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>


            </div>
        </div>
    </div>
</div>

