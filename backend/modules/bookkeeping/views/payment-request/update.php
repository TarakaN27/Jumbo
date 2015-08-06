<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 21.07.15
 */
use yii\helpers\Html;
$this->title = Yii::t('app/book','Update payment request');
?>
<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2><?= Html::encode($this->title) ?></h2>
                 <section class="pull-right">
                    <?= Html::a(Yii::t('app/book', 'To list'), ['index'], ['class' => 'btn btn-warning']) ?>
                 </section>
                 <div class="clearfix"></div>
            </div>

            <?= $this->render('/default/_payment_request_form', [
                'model' => $model,
            ]) ?>
        </div>
    </div>
</div>