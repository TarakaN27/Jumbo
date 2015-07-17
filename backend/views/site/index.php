<?php
/* @var $this yii\web\View */

$this->title = 'My Yii Application';

?>
<?=\common\components\widgets\lifeFeed\LifeFeedWidget::widget(['userID' => Yii::$app->user->id]);?>