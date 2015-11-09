<?php
/* @var $this yii\web\View */

$this->title = 'My Yii Application';

?>
<div class="row">
    <div class="col-md-6 col-sm-6 col-xs-12" >
<?=\common\components\widgets\liveFeed\LiveFeedWidget::widget(['userID' => Yii::$app->user->id]);?>
        </div>
    <div class="col-md-6 col-sm-6 col-xs-12">
        <?=\common\components\widgets\units\DashboardManUnits::widget([]);?>
    </div>
</div>