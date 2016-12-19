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
use yii\widgets\ActiveForm;
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
                        <?php echo \yii\helpers\Html::a(
                            Yii::t('app/crm','Add_new_company'),
                            ['/crm/company/create','is_partner' => \common\models\AbstractActiveRecord::YES],
                            ['class'=>'btn btn-primary']
                        );?>
                    <?php endif;?>
                </section>
                <div class = "clearfix"></div>
            </div>
            <div class = "x_content">
                    <div class="row">
                        <div class = "col-md-6 col-sm-6 col-xs-12 ">
                            <div class = "col-md-4 col-sm-4 col-xs-12">
                                <?php $form = ActiveForm::begin(['method'=>'get']);?>
                                <?=$form->field($searchModel,'beginDate')->widget(\kartik\date\DatePicker::className(),[
                                    'options' => [
                                        'class' => 'form-control'
                                    ],
                                    'pluginOptions' => [
                                        'autoclose' => TRUE,
                                        'format' => 'dd.mm.yyyy',
                                        'defaultDate' => date('d.m.Y', time())
                                    ]
                                ])?>
                            </div>
                            <div class = "col-md-4 col-sm-4 col-xs-12">
                                <?=$form->field($searchModel,'endDate')->widget(\kartik\date\DatePicker::className(),[
                                    'options' => [
                                        'class' => 'form-control'
                                    ],
                                    'pluginOptions' => [
                                        'autoclose' => TRUE,
                                        'format' => 'dd.mm.yyyy',
                                        'defaultDate' => date('d.m.Y', time())
                                    ]
                                ])?>
                            </div>
                            <div class="col-md-4 col-sm-4 col-xs-12 ppd-top-23">
                                <div class="form-group text-center">
                                    <?= Html::submitButton(Yii::t('app/reports', 'Get report'), ['class' => 'btn btn-success']) ?>
                                </div>
                            </div>
                            <div class = "col-md-6 col-sm-6 col-xs-12 ">
                                <?=$form->field($searchModel,'showByAllPayments')->checkbox();?>
                            </div>
                            <?php ActiveForm::end();?>
                        </div>


                    </div>
                <?php echo \common\components\widgets\WMCPageSize\WMCPageSize::widget();?>
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'filterSelector' => 'select[name="per-page"]',
                    'columns' => [
                        [
                            'attribute' => 'id',
                            'format' => 'raw',
                            'value' => function($model){
                                return Html::a($model->id,['view','id' => $model->id],['class'=>'link-upd']);
                            }
                        ],
                        [
                            'attribute' => 'corp_name',
                            'label' => Yii::t('app/users','Partner ID'),
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
                        'totalCurrentMonthSum:decimal',
                        'totalProcessedSum:decimal',
                        'totalPercentSum:decimal',
                        'availToWithdrawal:decimal',
                        [
                            'label' => '',
                            'format' => 'raw',
                            'value' => function($model){
                                $arLinks = [
                                    [
                                        'title' => Yii::t('app/users','Partner link lids'),
                                        'href' => ['link-lead','pid' => $model->id]
                                    ],
                                    [
                                        'title' => Yii::t('app/users','Partner allow services'),
                                        'href' => ['allow-services','id' => $model->id]
                                    ]
                                ];
                                return \common\components\helpers\CustomHtmlHelper::dropDownSettings($arLinks);
                            }
                        ],
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{view}{update}',
                            'buttons' => [
                                'update' => function ($url, $model) {

                                    $url = \yii\helpers\Url::to(['/crm/company/update','id' => $model->id]);

                                    return Html::a('<span class="glyphicon glyphicon-pencil"></span>', $url, [
                                        'title' => Yii::t('app', 'Update'),
                                    ]);
                                }
                            ],
                        ],
                    ]
                    
                    //'columns' => $columns
                ]); ?>
                <div class="row">
                    <h3><?=Yii::t('app/users','Total Sum');?></h3>
                    <table class="table table-bordered ">
                        <thead>
                        <tr>
                            <th><?=Yii::t('app/users','Total Current Month Sum');?></th>
                            <th><?=Yii::t('app/users','Total Processed Sum');?></th>
                            <th><?=Yii::t('app/users','Total Percent Sum');?></th>
                            <th><?=Yii::t('app/users','Avail To Withdrawal');?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>
                               <?=Yii::$app->formatter->asDecimal($total->totalCurrentMonthSum,2)?>           </td>
                            <td>
                                <?=Yii::$app->formatter->asDecimal($total->totalProcessedSum,2)?>            </td>
                            <td>
                                <?=Yii::$app->formatter->asDecimal($total->totalPercentSum,2)?>
                            </td>
                            <td>
                                <?=Yii::$app->formatter->asDecimal($total->availToWithdrawal,2)?>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>


