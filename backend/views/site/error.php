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
                        <h1 class="error-number">404</h1>
                        <h2>Sorry but we couldnt find this page</h2>
                        <p>This page you are looking for does not exsist
                        </p>
                        <div class="mid_center">
                           <p><?= nl2br(Html::encode($message)) ?></p>
                        </div>
                    </div>
                </div>
            </div>
