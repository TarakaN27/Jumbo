<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 20.07.15
 */
use yii\helpers\Html;
?>
<section class = "inner_dialog_section_msg li-msg"  data-id="<?=$msg->id;?>">
    <h2 class = "title ">

        <a><?php echo $msg->buser->getFio();?></a>
        <span class="time"><?php echo Yii::$app->formatter->asDatetime($msg->created_at); ?></span>
        <?php if($msg->buser_id == Yii::$app->user->id && $msg->technical != \common\models\Messages::YES):?>
            <?=Html::tag('span','<i class="fa fa-trash"></i>',[
                'data-id' => $msg->id,
                'class' => 'msg-trash'
            ])?>
            <?=Html::tag('span','<i class="fa fa-pencil-square"></i>',[
                'data-id' => $msg->id,
                'class' => 'msg-edit'
            ])?>
        <?php endif;?>

    </h2>
    <section class = "excerpt">
        <?php echo $msg->msg;?>
    </section>
</section>