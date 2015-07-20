<?php
/* @var $this yii\web\View */

$this->title = 'My Yii Application';

?>
<?=\common\components\widgets\liveFeed\LiveFeedWidget::widget(['userID' => Yii::$app->user->id]);?>