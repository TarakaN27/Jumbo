<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 28.07.15
 */
use yii\helpers\Html;
?>
<div class="mail_list" data-id="<?=$model->id;?>">
    <a href="#nogo" data-id="<?php echo $model->id;?>" class="dialog-mail">
        <div class="left">
            <i class="fa fa-circle"></i> <i class="fa fa-edit"></i>
        </div>
        <div class="right">
            <h3><?php echo is_object($obBuser = $model->owner) ? $obBuser->getFio() : 'N/A';?>
                <small><?php echo Yii::$app->formatter->asDatetime($model->created_at);?></small>
            </h3>
            <p><?=$model->theme;?></p>
        </div>
        <?php echo Html::hiddenInput('theme',$model->theme,['data-id' => $model->id]);?>
        <?php echo Html::hiddenInput('owner',is_object($obOwner = $model->owner) ? $obOwner->getFio() : 'N/A');?>
        <?php echo Html::hiddenInput('date',Yii::$app->formatter->asDatetime($model->created_at))?>
    </a>
</div>