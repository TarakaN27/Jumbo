<?php
/* @var $this yii\web\View */

$this->title = 'Webmart internal';

?>
<div class="row">

    <div class="col-md-12 col-sm-12 col-xs-12" >
<?=\common\components\widgets\liveFeed\LiveFeedWidget::widget(['userID' => Yii::$app->user->id]);?>
    </div>
    <!--div class="col-md-6 col-sm-6 col-xs-12">
        <?php
        /*
        \common\components\widgets\units\DashboardManUnits::widget([]);
        */

        ?>
    </div-->
</div>