<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $exception Exception */

$this->title = $name;
?>
<!-- page content -->
            <div class="col-md-12">
                <div class="col-middle">
                    <div class="text-center text-center">
                        <h1 class="error-number" style="margin-top: -18px;"><?=$name?></h1>
                        <p><?= nl2br(Html::encode($message)) ?></p>
                        <br/>
                        <?=Html::a(Yii::t('app/common','To home'),Yii::$app->getHomeUrl())?>
                    </div>
                </div>
            </div>