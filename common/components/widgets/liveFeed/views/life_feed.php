<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 16.07.15
 * Виджет добавляет живую ленту.
 */
use yii\helpers\Html;
use \vova07\imperavi\Widget as ImperaviWidget;
use \kartik\select2\Select2;
$this->registerCssFile('@web/css/editor/index.css');
//регистрируем переменные для работы скрипта
$this->registerJs('
var
    DIALOG_SEND_MSG_URL = "' . \yii\helpers\Url::to(['ajax-service/add-comment']) . '",
    DIALOG_ERROR_TITLE = "' . Yii::t('app/common', 'DIALOG_ERROR_TITLE') . '",
    DIALOG_EMPTY_ID_TEXT = "' . Yii::t('app/common', 'DIALOG_EMPTY_ID_TEXT') . '",
    DIALOG_EMPTY_ID_TEXT = "' . Yii::t('app/common', 'DIALOG_EMPTY_ID_TEXT') . '",
    DIALOG_EMPTY_MSG_TEXT = "' . Yii::t('app/common', 'DIALOG_EMPTY_MSG_TEXT') . '",
    DIALOG_SUCCESS_TITLE = "' . Yii::t('app/common', 'DIALOG_SUCCESS_TITLE') . '",
    DIALOG_SUCCESS_ADD_COMMENT = "' . Yii::t('app/common', 'DIALOG_SUCCESS_ADD_COMMENT') . '",
    DIALOG_SUCCESS_ADD_DIALOG = "' . Yii::t('app/common', 'DIALOG_SUCCESS_ADD_DIALOG') . '",
    DIALOG_ERROR_ADDCOMMENT = "'. Yii::t('app/common', 'DIALOG_ERROR_ADDCOMMENT') .'";
', \yii\web\View::POS_HEAD);
$this->registerJsFile('@web/js/wm_app/wm_live_feeds.js',
    [
        'depends' => [
            \backend\assets\AppAsset::className()
        ]
    ]
);
?>
<div class = "x_panel">
    <div class = "x_title">
        <h2><?= Yii::t('app/common', 'Live feed') ?></h2>
        <ul class = "nav navbar-right panel_toolbox">
            <li>
                <?echo Html::button(Yii::t('app/common','Add message').'<i class = "fa fa-chevron-down"></i>',[
                        'class' => 'btn btn-info btn-xs btn-msg-for-all',
                        'data' => '0'
                ])?>
            </li>
        </ul>
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
                        <button class = "btn btn-success btn-sm sendComment" data = "0" type = "button">
                            <?= Yii::t('app/common', 'Send comment') ?>
                        </button>
                    </div>
                </div>
            </form>
        </div>
        <!--END REDACTOR-->
    </div>
                            <!-- -->
    <div class = "x_content msgBoxList">
        <?= $this->render('_dialog_part', ['arDialogs' => $arDialogs]) ?>
    </div>
</div>
