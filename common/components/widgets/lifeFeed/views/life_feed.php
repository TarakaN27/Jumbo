<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 16.07.15
 * Виджет добавляет живую ленту.
 */
$this->registerCssFile('@web/css/editor/index.css');
//регистрируем переменные для работы скрипта
$this->registerJs('
var
    DIALOG_SEND_MSG_URL = "'.\yii\helpers\Url::to(['ajax-service/add-comment']).'",
    DIALOG_ERROR_TITLE = "'.Yii::t('app/common','DIALOG_ERROR_TITLE').'",
    DIALOG_EMPTY_ID_TEXT = "'.Yii::t('app/common','DIALOG_EMPTY_ID_TEXT').'",
    DIALOG_EMPTY_ID_TEXT = "'.Yii::t('app/common','DIALOG_EMPTY_ID_TEXT').'",
    DIALOG_EMPTY_MSG_TEXT = "'.Yii::t('app/common','DIALOG_EMPTY_MSG_TEXT').'";
',\yii\web\View::POS_HEAD);

$this->registerJsFile('@web/js/wm_app/wm_live_feeds.js', ['depends' => [\backend\assets\AppAsset::className()]]);
?>
<div class="x_panel">
                            <div class="x_title">
                                <h2><?=Yii::t('app/common','Live feed')?></h2>
                                <ul class="nav navbar-right panel_toolbox">
                                    <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                                    </li>
                                    <li class="dropdown">
                                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><i class="fa fa-wrench"></i></a>
                                        <ul class="dropdown-menu" role="menu">
                                            <li><a href="#">Settings 1</a>
                                            </li>
                                            <li><a href="#">Settings 2</a>
                                            </li>
                                        </ul>
                                    </li>
                                    <li><a class="close-link"><i class="fa fa-close"></i></a>
                                    </li>
                                </ul>
                                <div class="clearfix"></div>
                            </div>
                            <div class="x_content">
                                <ul class="list-unstyled timeline">
                                    <li>
                                        <div class="block">
                                            <div class="tags">
                                                <a href="" class="tag">
                                                    <span>Entertainment</span>
                                                </a>
                                            </div>
                                            <div class="block_content">
                                                <h2 class="title ">
                                                        <span><?php echo Yii::$app->formatter->asDatetime(time());?></span> от <a>Jane Smith</a>
                                                </h2>

                                                <p class="excerpt">
                                                    Film festivals used to be do-or-die moments for movie makers. They were where you met the producers that could fund your project, and if the buyers liked your flick, they’d pay to Fast-forward and…
                                                </p>
                                                <button class="btn btn-info btn-xs open_dialog_button" data="1"><?=Yii::t('app/common','Dialog');?> <i class="fa fa-chevron-down"></i></button>
                                                <div class="dialog_section" data-id="1">
                                                     <div class="block">
                                                        <div class="block_content">
                                                            <section class="inner_dialog_section_msg">
                                                                <h2 class="title ">
                                                                        <span><?php echo Yii::$app->formatter->asDatetime(time());?></span></span> by <a>Jane Smith</a>
                                                                </h2>
                                                                <p class="excerpt">
                                                                    Film festivals used to be do-or-die moments for movie makers. They were where you met the producers that could fund your project, and if the buyers liked your flick, they’d pay to Fast-forward and…
                                                                </p>
                                                            </section>
                                                            <section class="inner_dialog_section_msg">
                                                                <h2 class="title ">
                                                                        <span><?php echo Yii::$app->formatter->asDatetime(time());?></span></span> by <a>Jane Smith</a>
                                                                </h2>
                                                                <p class="excerpt">
                                                                    Film festivals used to be do-or-die moments for movie makers. They were where you met the producers that could fund your project, and if the buyers liked your flick, they’d pay to Fast-forward and…
                                                                </p>
                                                            </section>
                                                            <section class="inner_dialog_section_msg">
                                                                <h2 class="title ">
                                                                        <span>13 hours ago</span> by <a>Jane Smith</a>
                                                                </h2>
                                                                <p class="excerpt">
                                                                    Film festivals used to be do-or-die moments for movie makers. They were where you met the producers that could fund your project, and if the buyers liked your flick, they’d pay to Fast-forward and…
                                                                </p>

                                                            </section>
                                                            <section class="inner_dialog_section_msg">
                                                                <h2 class="title ">
                                                                        <span>13 hours ago</span> by <a>Jane Smith</a>
                                                                </h2>
                                                                <p class="excerpt">
                                                                    Film festivals used to be do-or-die moments for movie makers. They were where you met the producers that could fund your project, and if the buyers liked your flick, they’d pay to Fast-forward and…
                                                                </p>
                                                            </section>
                                                        </div>

                                                    </div>
                                                    <button class="btn btn-success btn-xs dialog_add_comment_btn" data="1"><?=Yii::t('app/common','Add comment')?> <i class="fa fa-chevron-down"></i></button>

                                                    <div class="redactor_panel" data-id="1">                                                         <!--Redactor-->
                                                         <div class="x_panel">
                                                             <form onsubmit="return false;" class="msgBox" data-id="1">
                                                                <div class="x_content">
                                                                    <?php echo \vova07\imperavi\Widget::widget([
                                                                        'name' => 'redactor',
                                                                        'settings' => [
                                                                            'lang' => 'ru',
                                                                            'minHeight' => 200,
                                                                            'plugins' => [
                                                                                'clips',
                                                                                'fullscreen'
                                                                            ]
                                                                        ]
                                                                    ]);
                                                                    ?>
                                                                    <br />
                                                                    <div class="form-group">
                                                                        <button class="btn btn-success btn-sm sendComment" data="1" type="button"><?=Yii::t('app/common','Send comment')?></button>
                                                                    </div>
                                                                </div>
                                                             </form>
                                                            </div>
                                                         <!--END REDACTOR-->
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="block">
                                            <div class="tags">
                                                <a href="" class="tag red_tag">
                                                    <span>Entertainment</span>
                                                </a>
                                            </div>
                                            <div class="block_content">
                                                <h2 class="title">
                                                    <a>Who Needs Sundance When You’ve Got&nbsp;Crowdfunding?</a>
                                                </h2>
                                                <div class="byline">
                                                    <span>13 hours ago</span> by <a>Jane Smith</a>
                                                </div>
                                                <p class="excerpt">Film festivals used to be do-or-die moments for movie makers. They were where you met the producers that could fund your project, and if the buyers liked your flick, they’d pay to Fast-forward and… <a>Read&nbsp;More</a>
                                                </p>
                                            </div>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="block">
                                            <div class="tags">
                                                <a href="" class="tag blue_tag">
                                                    <span>Entertainment</span>
                                                </a>
                                            </div>
                                            <div class="block_content">
                                                <h2 class="title">
                                                    <a>Who Needs Sundance When You’ve Got&nbsp;Crowdfunding?</a>
                                                </h2>
                                                <div class="byline">
                                                    <span>13 hours ago</span> by <a>Jane Smith</a>
                                                </div>
                                                <p class="excerpt">Film festivals used to be do-or-die moments for movie makers. They were where you met the producers that could fund your project, and if the buyers liked your flick, they’d pay to Fast-forward and… <a>Read&nbsp;More</a>
                                                </p>
                                            </div>
                                        </div>
                                    </li>
                                </ul>

                            </div>
                        </div>
</div>