<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use common\components\helpers\CustomHelper;
use common\components\helpers\CustomViewHelper;
use yii\web\View;
use yii\helpers\ArrayHelper;
use yii\web\JsExpression;
/* @var $this yii\web\View */
/* @var $searchModel common\models\search\ActsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app/book', 'Acts');
$this->params['breadcrumbs'][] = $this->title;
$this->registerCssFile('//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/css/bootstrap-editable.css');
CustomViewHelper::registerJsFileWithDependency('@web/js/parts/acts_index.js',$this);
CustomViewHelper::registerJsFileWithDependency('//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/js/bootstrap-editable.min.js',$this);
$em = new \yii\validators\EmailValidator();
$jsPattern = new \yii\web\JsExpression($em->pattern);
$this->registerJs("
var
    errorNotSelectedActs = '".Yii::t('app/book', 'You have not selected acts')."',
    urlSendAct = '".\yii\helpers\Url::to(['send-acts'])."',
    errorTitleSendAct = '".Yii::t('app/book','Sent act request')."',
    actsNotSent = '".Yii::t('app/book','Acts not sent')."',
    actSuccessSent = '".Yii::t('app/book','Acts successfully sent')."',
    actServerError = '".Yii::t('app/users','Server error')."',
    emailPattern = ".$jsPattern.";
",View::POS_HEAD);
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
                            'checkboxOptions' =>  function($model, $key, $index, $widget){
                                return [
                                    'class' => 'selectedActs'.($model->sent ? ' hide' : ''),
                                    'value' => $model->id
                                ];
                            }
                        ],
                        [
                            'attribute' => 'id'
                        ],
                        [
                            'attribute' => 'act_num',
                            'format' => 'html',
                            'visible' => function($model){
                                return false;
                            },
                            'value' => function($model){
                                return $model->act_num;

                            },
                        ],
                        'amount:decimal',
                        [
                            'attribute' => 'currency_id',
                            'value' => 'currency.code',
                            'filter' => \common\models\ExchangeRates::getRatesCodes()
                        ],
                        [
                            'attribute' => 'lp_id',
                            'value' => 'legalPerson.name',
                            'filter' => \common\models\LegalPerson::getLegalPersonMap()
                        ],
                        [
                            'attribute' => 'bank_id',
                            'value' => 'bankDetails.name',
                            'filter' => \common\models\BankDetails::getActiveBankDetails()
                        ],
                        [
                            'attribute' => 'cuser_id',
                            'value' => 'cuser.infoWithSite',
                            'filter' => \kartik\select2\Select2::widget([
                                'model' => $searchModel,
                                'attribute' => 'cuser_id',
                                'initValueText' => $cuserDesc, // set the initial display text
                                'options' => [
                                    'placeholder' => Yii::t('app/crm','Search for a company ...')
                                ],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                    'minimumInputLength' => 2,
                                    'ajax' => [
                                        'url' => \yii\helpers\Url::to(['/ajax-select/get-contractor']),
                                        'dataType' => 'json',
                                        'data' => new JsExpression('function(params) { return {q:params.term}; }')
                                    ],
                                    'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                                    'templateResult' => new JsExpression('function(cmp_id) { return cmp_id.text; }'),
                                    'templateSelection' => new JsExpression('function (cmp_id) { return cmp_id.text; }'),
                                ],
                            ]),
                        ],
                        [
                            'attribute' => 'cuser.requisites.c_email',
                            'format' => 'raw',
                            'value' => function($model){
                                return Html::a(ArrayHelper::getValue($model,'cuser.requisites.c_email'),'#',[
                                    'class' => 'editable',
                                    'data-value' => ArrayHelper::getValue($model,'cuser.requisites.c_email'),
                                    'data-type' => "text",
                                    'data-pk' => $model->cuser_id,
                                    //'data-created_by' => $model->created_by,
                                    // 'data-source' => \yii\helpers\Json::encode(CrmTask::getStatusArr()),
                                    'data-url' => \yii\helpers\Url::to(['update-cuser-email']),
                                    'data-title' => Yii::t('app/common','Изменить емаил')
                                ]);
                            }
                        ],
                        [
                            'attribute' => 'act_date',
                            'format' => 'date',
                            'filter' => \kartik\date\DatePicker::widget([
                                'model' => $searchModel,
                                'attribute' => 'from_date',
                                'attribute2' => 'to_date',
                                'options' => ['placeholder' => Yii::t('app/crm','Begin date')],
                                'options2' => ['placeholder' => Yii::t('app/crm','End date')],
                                'type' => \kartik\date\DatePicker::TYPE_RANGE,
                                'separator' => '-',
                                'pluginOptions' => [
                                    //'autoclose' => true,
                                    'format' => 'dd.mm.yyyy',
                                    'defaultDate' => date('d.m.Y',time())
                                ],
                            ]),
                        ],
                        [
                            'attribute' => 'sent',
                            'format' => 'raw',
                            'value' => function($model){
                                return Html::tag('span',$model->getYesNoStr($model->sent),['id' => 'sSent_'.$model->id]);
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
                            'template' => '{view}'
                        ],
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{delete}'
                        ],
                    ],
                ]); ?>
                <div class="col-md-4 ">
                    <?php if(!empty($iTotal)):?>
                        <?=Html::tag('h3',Yii::t('app/crm','Total').' <small>'.$iTotal.'</small>')?>
                    <?php endif;?>
                </div>
                        </div>
        </div>
    </div>
</div>
