<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 20.07.15
 */
use yii\helpers\Html;
use \vova07\imperavi\Widget as ImperaviWidget;
?>

<?php foreach($arDialogs as $dialog):?>
    <ul class = "list-unstyled timeline">
            <li id="dialogBlockId_<?php echo $dialog['dialog']->id;?>">
                <div class = "block">
                    <div class = "tags">
                        <a href = "" class = "tag <?php echo $dialog['dialog']->getTagClass();?>">
                            <span><?php echo $dialog['dialog']->getTypeStr();?></span>
                        </a>
                    </div>
                    <div class = "block_content">
                        <h2 class = "title ">
                            <span><?php echo Yii::$app->formatter->asDatetime($dialog['dialog']->created_at); ?></span> от <a><?php echo $dialog['dialog']->owner->getFio();?></a>
                        </h2>

                        <p class = "excerpt">
                            <?php echo $dialog['firstMsg'];?>
                        </p>
                        <button class = "btn btn-info btn-xs open_dialog_button" data = "<?php echo $dialog['dialog']->id;?>"><?= Yii::t('app/common', 'Dialog'); ?>
                            <i class = "fa fa-chevron-down"></i>
                        </button>
                        <div class = "dialog_section" data-id = "<?php echo $dialog['dialog']->id;?>">
                            <div class = "block">
                                <div class = "block_content">
                                    <?php foreach($dialog['msg'] as $msg):?>
                                        <?= $this->render('_dialog_msg', ['msg' => $msg]) ?>
                                    <?php endforeach;?>
                                </div>
                            </div>
                            <button class = "btn btn-success btn-xs dialog_add_comment_btn"
                                    data = "<?php echo $dialog['dialog']->id;?>"><?= Yii::t('app/common', 'Add comment') ?> <i
                                    class = "fa fa-chevron-down"></i>
                            </button>
                            <div class = "redactor_panel" data-id = "<?php echo $dialog['dialog']->id;?>">
                            <!--Redactor-->
                                <div class = "x_panel">
                                    <form onsubmit = "return false;" class = "msgBox" data-id = "<?php echo $dialog['dialog']->id;?>">
                                        <?php echo Html::hiddenInput('dialog_id',$dialog['dialog']->id); ?>
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
                                            <br />
                                            <div class = "form-group">
                                                <button class = "btn btn-success btn-sm sendComment" data = "<?php echo $dialog['dialog']->id;?>" type = "button">
                                                    <?= Yii::t('app/common', 'Send comment') ?>
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <!--END REDACTOR-->
                            </div>
                        </div>
                    </div>
                </div>
            </li>
        </ul>
<?php endforeach;?>