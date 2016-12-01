<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 20.07.15
 */
use yii\helpers\Html;
use \vova07\imperavi\Widget as ImperaviWidget;
use yii\web\JsExpression;
$page = isset($pages) && is_object($pages) ? $pages->getPage() : 0;
?>

<?php foreach($arDialogs as $dialog):?>
    <ul class = "list-unstyled timeline" data-pages = "<?=$page?>">
            <li id="dialogBlockId_<?php echo $dialog['dialog']->id;?>" class="
            <?php if(in_array($dialog['dialog']->id,$arRedisDialog)):?>
                    dialog-not-viewed
            <?php endif;?>
            ">
                <div class = "block">
                    <div class = "tags">
                        <a target="_blank" href = "<?=$dialog['dialog']->getLinkForEntity();?>" class = "tag <?php echo $dialog['dialog']->getTagClass();?>">
                            <span><?php echo $dialog['dialog']->getTypeStr();?> <small><?php echo $dialog['dialog']->getNumber();?></small></span>
                        </a>
                    </div>
                    <div class = "block_content">
                        <h2 class = "title ">
                            <span><?php echo Yii::$app->formatter->asDatetime($dialog['dialog']->created_at); ?></span> от <a><?php echo $dialog['dialog']->owner->getFio();?></a>
                        </h2>

                        <p class = "excerpt">
                            <?php echo $dialog['dialog']->theme;?>
                        </p>
                        <button data-viewed="<?php if(in_array($dialog['dialog']->id,$arRedisDialog)):?>no<?php endif;?>" class = "btn btn-info btn-xs open_dialog_button" data = "<?php echo $dialog['dialog']->id;?>"><?= Yii::t('app/common', 'Dialog'); ?>
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
                                            <div style="margin-top: -20px;" class="form-group field-crmtask-priority required">
                                                <div class="form-group">
                                                    <?echo \kato\DropZone::widget([
                                                        'id'=> 'dropzoneComment'.$dialog['dialog']->id,
                                                        'dropzoneContainer' => 'dropzoneComment'.$dialog['dialog']->id,
                                                        'previewsContainer' => 'dropzoneCommentpreview'.$dialog['dialog']->id,
                                                        'uploadUrl'=>\yii\helpers\Url::to(['/crm/task/upload-file/']),
                                                        'options'=>
                                                            ['addRemoveLinks'=> 'true',
                                                                'removedfile' => new JsExpression("function(file) {
                                    var name = file.name;        
                                    $.ajax({
                                        type: 'POST',
                                        url: '/service/crm/task/file-delete',
                                        data: 'id='+file.xhr.response,
                                        dataType: 'html'
                                    });
                                var _ref;
                                return (_ref = file.previewElement) != null ? _ref.parentNode.removeChild(file.previewElement) : void 0;        
                                }"),
                                                                'thumbnailWidth'=> 90,
                                                                'thumbnailHeight'=> 90,
                                                                'dictDefaultMessage' => Yii::t('app/crm', 'Drop file'),
                                                                'dictCancelUpload' => Yii::t('app/crm', 'Cancel upload'),
                                                                'dictRemoveFile'=>Yii::t('app/crm', 'Remove file'),
                                                            ],
                                                        'clientEvents'=>[
                                                            'complete' => "function(file){
                                $('#dropzoneComment".$dialog['dialog']->id." .dropzone-previews').append(\"<input type='hidden' name='dropZoneFiles[]' value='\"+file.xhr.response+\"'>\");                           
                            }",
                                                        ]
                                                    ]);?>
                                                </div>
                                            </div>
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

<?php if(!empty($pages)):
    $currPage = $pages->getPage();
    $coutPage = $pages->getPageCount();
    if($coutPage > $currPage+1):
    ?>
<div class="col-md-12 text-center loadMoreBlock">
    <?=Html::button(Yii::t('app/common','Load more'),[
        'class' => 'btn btn-default',
        'onclick' => 'loadMoreLiveFeedDialogs("'.($currPage+1).'");',
    ]);?>
</div>
<?php endif; endif;?>