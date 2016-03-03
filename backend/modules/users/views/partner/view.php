<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\bootstrap\ActiveForm;
/* @var $this yii\web\View */
/* @var $model common\models\Partner */

$this->title = $model->getFio();
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/users', 'Partners'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$obPurse = \common\models\PartnerPurse::getPurse($model->id);

$this->registerJs("
function reloadPage()
{
    location.reload();
}
",\yii\web\View::POS_END);

$this->registerJs("
$('#loader').hide();
$('#w0').on('beforeSubmit', function () {
    $('#btnSend').hide();
    $('#loader').show();

    var
        data = $('#w0').serialize();
    $.ajax({
                type: \"POST\",
                cache: false,
                url: '".\yii\helpers\Url::to(['connect-csda'])."',
                dataType: \"json\",
                data: data,
                success: function(msg){
                    if(!msg)
                      {
                            $('#btnSend').show();
                            $('#loader').hide();
                            addWarningNotify('".Yii::t('app/users','CSDA partner create account request')."','".Yii::t('app/user','CSDA partner create account FAIL')."');

                      }else{
                            if(msg.error != '')
                            {
                                $('#btnSend').show();
                                $('#loader').hide();
                                addErrorNotify('".Yii::t('app/users','CSDA partner create account request')."',msg.error);
                            }
                            else
                            {
                                $('#loader').hide();
                                addSuccessNotify('".Yii::t('app/users','CSDA partner create account request')."','".Yii::t('app/users','CSDA account successfully created')."');
                                 setTimeout(reloadPage,2000);
                            }
                      }
                },
                error: function(msg){
                    addErrorNotify('".Yii::t('app/users','CSDA partner create account request')."','".Yii::t('app/users','Server error')."');
                    return false;
                }
            });

    return  false;
});
$('.btn-psk').on('click',function(){
            $.ajax({
                type: \"POST\",
                cache: false,
                url: '".\yii\helpers\Url::to(['disconnect-csda'])."',
                dataType: \"json\",
                data: {psk:'".$model->psk."',pid:'".$model->id."'},
                success: function(msg){
                    if(msg)
                    {
                        addSuccessNotify('".Yii::t('app/users','CSDA partner remove account request')."','".Yii::t('app/users','CSDA account successfully deleted')."');
                        setTimeout(reloadPage,2000);
                    }else{
                         addErrorNotify('".Yii::t('app/users','CSDA partner remove account request')."','".Yii::t('app/users','Error. Remove account')."');
                    }
                },
                error: function(msg){
                    addErrorNotify('".Yii::t('app/users','CSDA partner remove account request')."','".Yii::t('app/users','Server error')."');
                    return false;
                }
            });
});
",\yii\web\View::POS_READY)

?>
    <div class = "row">
        <div class = "col-md-12 col-sm-12 col-xs-12">
            <div class = "x_panel">
                <div class = "x_title">
                    <h2><?= Html::encode($model->getFio()) ?></h2>
                    <section class="pull-right">
                        <?=  Html::a(Yii::t('app/users', 'To list'), ['index'], ['class' => 'btn btn-warning']) ?>
                        <?= Html::a(Yii::t('app/users', 'Create Partner'), ['create'], ['class' => 'btn btn-success']) ?>
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
                    <!-- modals -->
                    <!-- Large modal -->
                    <!--button type="button" class="btn btn-primary" data-toggle="modal" data-target=".bs-example-modal-lg">Large modal</button-->
                    <div class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">

                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span>
                                    </button>
                                    <h4><?=Yii::t('app/users','External csda account title')?></h4>
                                </div>
                                <?php $form = ActiveForm::begin([
                                    'options' => [
                                        'class' => 'form-horizontal form-label-left',
                                        'onsubmit' => 'addPartnerPsk();return false;'
                                    ],
                                    'fieldConfig' => [
                                        'template' => '<div class="form-group">{label}<div class="col-md-6 col-sm-6 col-xs-12">{input}</div><ul class="parsley-errors-list" >{error}</ul></div>',
                                        'labelOptions' => ['class' => 'control-label col-md-3 col-sm-3 col-xs-12'],
                                    ],
                                ]);
                                    echo Html::activeHiddenInput($obForm,'partnerID');
                                ?>
                                <div class="modal-body">


                                    <?= $form->field($obForm, 'username')->textInput(['maxlength' => true]) ?>
                                    <?= $form->field($obForm, 'email')->textInput(['maxlength' => true]) ?>
                                    <?= $form->field($obForm, 'password')->passwordInput(['maxlength' => true]) ?>
                                    <?= $form->field($obForm, 'passwordRepeat')->passwordInput(['maxlength' => true]) ?>
                                    <div id="validError"></div>
                                </div>
                                <div class="modal-footer">
                                    <div class="loader" id="loader"></div>
                                    <?= Html::submitButton(Yii::t('app/users', 'Create'), ['class' => 'btn btn-success','id' => 'btnSend' ]) ?>
                                </div>
                                <?php ActiveForm::end(); ?>

                            </div>
                        </div>
                    </div>

                        <?= DetailView::widget([
                            'model' => $model,
                            'attributes' => [
                                'fname',
                                'lname',
                                'mname',
                                'email:email',
                                'phone',
                                [
                                    'attribute' => 'psk',
                                    'format' => 'raw',
                                    'value' => '<span id="spanPsk">'.$model->psk.'</span>' . (empty($model->psk) ?
                                        Html::button(
                                            Yii::t('app/users','Create'),
                                            [
                                                'class' => 'btn btn-success',
                                                'data' => 'add-psk',
                                                'data-toggle'=>"modal",
                                                'data-target'=>".bs-example-modal-lg"
                                            ]) :
                                        Html::button(Yii::t('app/users','Remove'),
                                            [
                                                'class' => 'btn btn-success btn-psk',
                                                'data' => 'remove-psk',
                                                'data-psk' => $model->psk
                                            ]))
                                ],
                                'description:ntext',
                                'post_address:ntext',
                                'ch_account:ntext',
                                [
                                    'attribute' => 'status',
                                    'value' => $model->getStatusStr()
                                ],
                                'created_at:datetime',
                                'updated_at:datetime',
                            ],
                        ]) ?>

                        <hr>
                    <h3><?=Yii::t('app/users', 'Purse')?>:</h3>
                    <?php if($obPurse):?>
                        <?= DetailView::widget([
                            'model' => \common\models\PartnerPurse::getPurse($model->id),
                            'attributes' => [
                                'payments',
                                'acts',
                                'amount',
                            ]
                        ])?>
                    <?php else:?>
                        <p><?=Yii::t('app/users', 'The partner does not have a purse')?></p>

                    <?php endif;?>
                </div>
            </div>
        </div>
    </div>
