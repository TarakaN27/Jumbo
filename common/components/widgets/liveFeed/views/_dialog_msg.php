<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 20.07.15
 */
?>
<section class = "inner_dialog_section_msg">
    <h2 class = "title ">
        <span><?php echo Yii::$app->formatter->asDatetime($msg->created_at); ?></span>
        <?php echo Yii::t('app/common','by')?> <a><?php echo $msg->buser->getFio();?></a>
    </h2>
    <p class = "excerpt">
        <?php echo $msg->msg;?>
    </p>
</section>