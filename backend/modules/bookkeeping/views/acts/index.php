<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
/* @var $this yii\web\View */
/* @var $searchModel common\models\search\ActsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app/book', 'Acts');
$this->params['breadcrumbs'][] = $this->title;

$this->registerJs("
function sendActs()
{
    var
       items = $('.selectedActs:checked');

    if(items == undefined || items.length == 0)
    {
        alert('".Yii::t('app/book', 'You have not selected acts')."');
        return false;
    }
    $.ajax({
                type: \"POST\",
                cache: false,
                url: '".\yii\helpers\Url::to(['send-acts'])."',
                dataType: \"json\",
                data: items.serialize(),
                success: function(msg){
                    if(!msg)
                      {
                            addErrorNotify('".Yii::t('app/book','Sent act request')."','".Yii::t('app/book','Acts not sent')."');
                      }else{
                            addSuccessNotify('".Yii::t('app/book','Sent act request')."','".Yii::t('app/book','Acts successfully sent')."');
                            location.reload();
                      }
                },
                error: function(msg){
                    addErrorNotify('".Yii::t('app/book','Sent act request')."','".Yii::t('app/users','Server error')."');
                    return false;
                }
            });
}


",\yii\web\View::POS_END);




$this->registerJs("
$('#sendActID').on('click',sendActs);
",\yii\web\View::POS_READY);
?>

<div class = "row">
    <div class = "col-md-12 col-sm-12 col-xs-12">
        <div class = "x_panel">
            <div class = "x_title">
                <h2><?= Html::encode($this->title) ?></h2>
                <section class="pull-right">
                    <?= Html::a(Yii::t('app/book', 'Create Acts'), ['create'], ['class' => 'btn btn-success']) ?>
                    <?= Html::button(Yii::t('app/book', 'Send acts'),['class' => 'btn btn-warning', 'id' => 'sendActID'])?>
                </section>
                <div class = "clearfix"></div>
            </div>
            <div class = "x_content">
                <?php echo \common\components\widgets\WMCPageSize\WMCPageSize::widget(); ?>
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'filterSelector' => 'select[name="per-page"]',
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],
                        [
                            'class' => 'yii\grid\CheckboxColumn',
                            'checkboxOptions' => [
                                'class' => 'selectedActs'
                            ]
                        ],
                        [
                            'attribute' => 'act_num',
                            'format' => 'html',
                            'value' => function($model){
                                return Html::a($model->act_num,['update','id' => $model->id],['class' => 'link-upd']);
                            }
                        ],
                        [
                            'attribute' => 'amount',
                            'format' => 'html',
                            'value' => function($model){
                                return Html::a($model->amount,['update','id' => $model->id],['class' => 'link-upd']);
                            }
                        ],
                        [
                            'attribute' => 'cuser_id',
                            'value' => function($model){
                                return is_object($obCuser = $model->cuser) ? $obCuser->getInfo() : $model->cuser_id;
                            },
                            'filter' => \common\models\CUser::getContractorMap()
                        ],
                        [
                            'attribute' => 'service_id',
                            'value' => function($model){
                                return is_object($obServ = $model->service) ? $obServ->name : $model->service_id;
                            },
                            'filter' => \common\models\Services::getServicesMap()
                        ],
                        'act_date',
                        [
                            'attribute' => 'sent',
                            'value' => function($model){
                                return $model->getYesNoStr($model->sent);
                            },
                            'filter' => \common\models\Acts::getYesNo()
                        ],

                        [
                            'attribute' => 'buser_id',
                            'value' => function($model){
                                return is_object($obBuser = $model->buser) ? $obBuser->getFio() : $model->buser_id;
                            },
                            'filter' => \backend\models\BUser::getAllMembersMap()
                        ],
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{dwld}',
                            'buttons' => [
                                'dwld' => function($url, $model, $key){
                                    $options = [
                                        'title' => Yii::t('app/common', 'Download'),
                                        'aria-label' => Yii::t('app/common', 'Download'),
                                        'target' => '_blank',
                                    ];
                                    $url = \yii\helpers\Url::to(['download-file','ask' => $model->ask]);
                                    return Html::a('<span class="glyphicon glyphicon-download-alt"></span>', $url, $options);
                                }
                            ]
                        ],
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{update}{view}'
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
