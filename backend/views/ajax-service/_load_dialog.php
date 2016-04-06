<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 27.07.15
 */
use yii\helpers\Html;
$iCounter = 0;
if($addLoadMoreBTN)
{
$currPage = $pages->getPage();
$coutPage = $pages->getPageCount();
}else{
    $currPage = 0;
    $coutPage = 0;
}
?>
<?php if($addLoadMoreBTN && $coutPage > $currPage+1):?>
<div class="loadMoreMsg">
    <?php echo \yii\helpers\Html::button(Yii::t('app/msg','Load more message'),[
        'data-page' => $currPage+1,
        'data-d-id' => $iDID,
        'class' => 'loadMoreBtn'
    ])?>
</div>
<?php endif;?>
<?php foreach($models as $model):?>
<blockquote class="<?php if($iCounter %2 != 0):?>blockquote-reverse<?php endif;?> li-msg" data-id="<?=$model->id;?>" >
    <section class="msg">
        <?php echo $model->msg;?>
    </section>
    <footer>
        <?php echo is_object($obBUser = $model->buser) ? $obBUser->getFio() : 'N/A'?> <?=Yii::t('app/common','at')?>
         <cite title="Source Title">
            <?=Yii::$app->formatter->asDatetime($model->created_at);?>
        </cite>
        <?php if($model->buser_id == Yii::$app->user->id):?>
            <?=Html::tag('span','<i class="fa fa-trash"></i>',[
                'data-id' => $model->id,
                'class' => 'msg-trash'
            ])?>
            <?=Html::tag('span','<i class="fa fa-pencil-square"></i>',[
                'data-id' => $model->id,
                'class' => 'msg-edit'
            ])?>
        <?php endif;?>
    </footer>
</blockquote>
<?php $iCounter++; endforeach;?>