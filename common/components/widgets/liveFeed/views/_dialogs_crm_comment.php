<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 14.12.15
 * Time: 17.35
 */
use yii\helpers\Html;
?>
<?php if(!is_null($pag)):?>
    <?php if($pag->getPageCount() > $pag->getPage()+1): $links = $pag->getLinks();?>
        <?=Html::button(Yii::t('app/common','Load more'),[
            'data-url' => $links[\yii\data\Pagination::LINK_NEXT],
            'class' => 'btn btn-default btn-load-more-comment',
            'data-d-id' => $dID,
            'onclick' => !isset($disableClick) ? 'loadMoreComments();' : ''
        ])?>
    <?php endif;?>
<?endif;?>
<?php foreach($models as $model):?>
	<li class="li-msg" data-id="<?=$model->id;?>">
        <div class="imgCmpMsg">
            <img src="/service/images/defaultUserAvatar.jpg" class="avatar" alt="img">
        </div>
        <div class="bodyCmpMsg">
            <div>
                    <span><strong><?php echo is_object($obUser = $model->buser)? $obUser->getFio() : $model->buser_id;?></strong></span>
                    <?php if($model->buser_id == Yii::$app->user->id && $model->technical != \common\models\Messages::YES):?>
                        <?=Html::tag('span','<i class="fa fa-trash"></i>',[
                            'data-id' => $model->id,
                            'class' => 'msg-trash'
                        ])?>
                        <?=Html::tag('span','<i class="fa fa-pencil-square"></i>',[
                            'data-id' => $model->id,
                            'class' => 'msg-edit'
                        ])?>
                    <?php endif;?>
                    <span class="time"><i class="fa fa-clock-o"></i> <?=Yii::$app->formatter->asDatetime($model->created_at);?></span>
            </div>
            <section class="message">
                    <?=$model->msg;?>
            </section>
        </div>
	</li>
<?php endforeach;?>
