<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\search\BuserInviteCodeSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app/users', 'Buser Invite Codes');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class = "row">
    <div class = "col-md-12 col-sm-12 col-xs-12">
        <div class = "x_panel">
            <div class = "x_title">
                <h2><?= Html::encode($this->title) ?></h2>
                <section class="pull-right">
                    <?php echo \yii\helpers\Html::a(Yii::t('app/users','Add_invite'),['add-invite'],['class'=>'btn btn-warning']);?>
                </section>
                <div class = "clearfix"></div>
            </div>
            <div class = "x_content">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],
                        'code',
                        'email:email',
                        [
                            'attribute' => 'user_type',
                            'value' => function($model){
                                return $model->getUserTypeStr();
                            },
                            'filter' => \backend\models\BUser::getRoleArr()

                        ],
                        [
                            'attribute' => 'buser_id',
                            'value' => function($model){
                                return is_object($obBuser = $model->buser) ? $obBuser->getFio() : $model->buser_id;
                            },
                            'filter' => \backend\models\BUser::getAllMembersMap()
                        ],
                        [
                            'attribute' => 'status',
                            'value' => function($model){
                                return $model->getStatusStr();
                            },
                            'filter' => \common\models\BuserInviteCode::getStatusArr()
                        ],
                        [
                            'attribute' => 'created_at',
                            'value' => function($model){
                                return Yii::$app->formatter->asDatetime($model->created_at);
                            },
                            'filter' => \yii\jui\DatePicker::widget([

                                'model'=>$searchModel,
                                'attribute'=>'created_at',
                                'language' => 'ru',
                                'dateFormat' => 'dd-MM-yyyy',
                                'options' =>['class' => 'form-control'],
                                'clientOptions' => [
                                    'defaultDate' => date('d-m-Y',time())
                                ],
                            ]),
                        ],
                        // 'created_at',
                        // 'updated_at',
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{resend}',
                            'buttons' =>[
                                'resend' => function($url, $model, $key){
                                    $options = [
                                        'title' => Yii::t('app/users', 'Resend'),
                                        'aria-label' => Yii::t('app/users', 'Resend'),

                                    ];
                                    $url = \yii\helpers\Url::to(['resend','id' => $model->id]);
                                    return Html::a('<span class="glyphicon glyphicon-share"></span>', $url, $options);
                                }
                            ],

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
