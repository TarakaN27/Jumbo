<?php

use yii\widgets\LinkPager;
use yii\helpers\Html;
use vova07\imperavi\Widget as ImperaviWidget;
use kartik\select2\Select2;
$this->title = Yii::t('app/msg','Dialogs');

$this->registerJs("
var
    DIALOG_ERROR_TITLE = '" . Yii::t('app/common', 'DIALOG_ERROR_TITLE') . "',
    DIALOG_SUCCESS_ADD_COMMENT ='" . Yii::t('app/common', 'DIALOG_SUCCESS_ADD_COMMENT') . "',
    DIALOG_SUCCESS_ADD_DIALOG = '" . Yii::t('app/common', 'DIALOG_SUCCESS_ADD_DIALOG') . "',
    DIALOG_ERROR_ADDCOMMENT = '". Yii::t('app/common', 'DIALOG_ERROR_ADDCOMMENT') ."',
    DIALOG_ERROR_LOAD_CONTENT = '". Yii::t('app/common', 'DIALOG_ERROR_LOAD_CONTENT') ."',
    DIALOG_ADD_MSG_URL = '".\yii\helpers\Url::to(['/ajax-service/add-new-message'])."',
    DIALOG_EMPTY_MSG_TEXT = '" . Yii::t('app/common', 'DIALOG_EMPTY_MSG_TEXT') . "',
    DIALOG_ADD_DIALOG_URL = '".\yii\helpers\Url::to(['/ajax-service/add-dialog'])."',
    DIALOG_SUCCESS_ADD_DIALOG = '" . Yii::t('app/common', 'DIALOG_SUCCESS_ADD_DIALOG') . "',
    DIALOG_LOAD_MSG_URL = '".\yii\helpers\Url::to(['/ajax-service/load-dialog'])."';
",\yii\web\View::POS_HEAD);

$this->registerJsFile('@web/js/wm_app/messenger.js',
    [
        'depends' => [
            \backend\assets\AppAsset::className()
        ]
    ]
);

$this->registerCss("
.mail_list.active{
    background:none repeat scroll 0 0 rgba(38, 185, 154, 0.16) ;
}
");
?>
<div class = "row">
    <div class = "col-md-12 col-sm-12 col-xs-12">
        <div class = "x_panel">
            <div class = "x_title">
                <h2><?php echo $this->title?></h2>
                <section class="pull-right">
                    <?php echo \yii\helpers\Html::button(Yii::t('app/msg','Add new dialog').' '.'<i class = "fa fa-chevron-down"></i>',['id' => 'add_new_dialog_id'])?>
                </section>
                <div class = "clearfix"></div>
            </div>
            <div class = "x_content msgBoxAll" data-id = "0">
        <!--Redactor-->
            <div class = "x_panel">
                <form onsubmit = "return false;" class = "msgBox" data-id = "0">
                    <?php echo Html::hiddenInput('dialog_id', 0); ?>
                    <?php echo Html::hiddenInput('author_id', Yii::$app->user->id); ?>
                    <div class = "x_content">
                        <?php echo Html::label(Yii::t('app/common','Message'))?>
                        <?php echo ImperaviWidget::widget([
                            'name' => 'redactor',
                            'settings' => [
                                'lang' => 'ru',
                                'minHeight' => 200,
                                'plugins' => [
                                    'clips',
                                    'fullscreen'
                                ]
                            ]
                        ]);?>
                        <?php echo Html::label(Yii::t('app/common','For users'))?>
                        <?php echo Select2::widget([
                            'name' => 'for_users',
                            'data' => \backend\models\BUser::getAllMembersMap(Yii::$app->user->id),
                            'options' => ['placeholder' => Yii::t('app/common','Select users'), 'multiple' => true],
                            'pluginOptions' => [
                                'tags' => true,
                                'maximumInputLength' => 10
                            ],
                        ]);?>
                        <br />
                        <div class = "form-group">
                            <button class = "btn btn-success btn-sm addNewDialog" data = "0" type = "button">
                                <?= Yii::t('app/common', 'Send comment') ?>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <!--END REDACTOR-->
        </div>

            <div class = "x_content">

                <div class="row">
                                        <div class="col-sm-3 mail_list_column">
                                            <?php foreach($models as $model):?>
                                                <?= $this->render('_dialog_left_part', ['model' => $model]) ?>
                                            <?php endforeach;?>
                                        </div>
                                        <!-- /MAIL LIST -->

                                        <!-- CONTENT MAIL -->
                                        <div class="col-sm-9 mail_view" >
                                            <div class="inbox-body">
                                                <div class="mail_heading row">
                                                    <div class="col-md-8">

                                                    </div>
                                                    <div class="col-md-4 text-right">
                                                        <p class="date" id="dialog-date"> </p>
                                                    </div>
                                                    <div class="col-md-12 " >
                                                        <h4 id="dialog-theme"> </h4>
                                                    </div>
                                                </div>
                                                <div class="sender-info">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <?=Yii::t('app/common','From');?>
                                                            <strong id="dialog-owner"></strong>
                                                            <?=Yii::t('app/common','To');?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="view-mail">

                                                </div>
                                                <div class="compose-btn pull-left">
                                                    <button class="btn btn-sm btn-add-comment"><i class="fa fa-reply"></i> Add comment</button>
                                                    </button>
                                                </div>
                                                 <div class = "clearfix"></div>
                                                    <div id="redactorBlock" class="blockRedactor">
                                                        <?php echo Html::label(Yii::t('app/common','Message'))?>
                                                        <?php echo ImperaviWidget::widget([
                                                            'name' => 'redactor',
                                                            'settings' => [
                                                                'lang' => 'ru',
                                                                'minHeight' => 200,
                                                                'plugins' => [
                                                                    'clips',
                                                                    'fullscreen'
                                                                ]
                                                            ]
                                                        ]);?>
                                                        <br />
                                                        <div class = "form-group">
                                                            <button class = "btn btn-success btn-sm sendComment" type = "button" onclick="sendComment();">
                                                                <?= Yii::t('app/common', 'Send comment') ?>
                                                            </button>
                                                        </div>
                                                    </div>
                                            </div>

                                        </div>
                                        <!-- /CONTENT MAIL -->
                                    </div>
                <?php echo LinkPager::widget([
                        'pagination' => $pages,
                    ]);
                ?>
            </div>
        </div>
    </div>
</div>