<?php

$this->title = Yii::t('app/crm','Crm feed');

?>
<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2><?=\yii\helpers\Html::decode($this->title)?></h2>
                <div class="clearfix"></div>
                <div class="crm-control">
                    <button class="btn btn-default"><?php echo Yii::t('app/crm','Crm feed')?></button>
                </div>
            </div>
            <div class="x_content">
                <?=\common\components\widgets\liveFeed\LiveFeedWidget::widget(['userID' => Yii::$app->user->id]);?>
            </div>
        </div>
    </div>
</div>