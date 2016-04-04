<?php
/**
 * Created by PhpStorm.
 * Corp: ZM_TEAM
 * User: E. Motuz
 * Date: 4/2/16
 * Time: 12:47 PM
 */
use yii\helpers\Html;
?>
<!-- /sidebar menu -->
<div class="sidebar-footer hidden-small">
    <?php if(!Yii::$app->user->isGuest): ?>
        <?=Html::a(
            '<span class="glyphicon glyphicon-plus" aria-hidden="true"></span>З',
            ['/crm/task/create'],
            [
                'data-toggle' => 'tooltip',
                'data-placement' => 'top',
                'data-original-title' => Yii::t('app/common','Add task'),
                'target' => '_blank'
            ]
        )?>
    <?php endif; ?>
    <?php if(
        Yii::$app->user->can('adminRights') ||
        Yii::$app->user->can('only_bookkeeper') ||
        Yii::$app->user->can('only_manager') ||
        Yii::$app->user->can('only_jurist')
    ):?>
        <?=Html::a(
            '<span class="glyphicon glyphicon-plus" aria-hidden="true"></span>К',
            ['/crm/company/create'],
            [
                'data-toggle' => 'tooltip',
                'data-placement' => 'top',
                'data-original-title' => Yii::t('app/common','Add company'),
                'target' => '_blank'
            ]
        )?>
    <?php endif;?>
    <?php if(!Yii::$app->user->isGuest && (Yii::$app->user->can('only_manager') || Yii::$app->user->can('adminRights'))): ?>
        <?=Html::a(
            '<span class="glyphicon glyphicon-plus" aria-hidden="true"></span>С',
            ['/documents/bills/create'],
            [
                'data-toggle' => 'tooltip',
                'data-placement' => 'top',
                'data-original-title' => Yii::t('app/common','Add bill'),
                'target' => '_blank'
            ]
        )?>
    <?php endif;?>
    <!---Обещанные платежи-------->
    <?php if(Yii::$app->user->can('adminRights') || Yii::$app->user->can('only_manager') || Yii::$app->user->can('only_bookkeeper')):?>
        <?=Html::a(
            '<span class="glyphicon glyphicon-plus" aria-hidden="true"></span>ОП',
            ['/bookkeeping/promised-payment/create'],
            [
                'data-toggle' => 'tooltip',
                'data-placement' => 'top',
                'data-original-title' => Yii::t('app/common','Add promised payment'),
                'target' => '_blank'
            ]
        )?>
    <?php endif;?>
</div>