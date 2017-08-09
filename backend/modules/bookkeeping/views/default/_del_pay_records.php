<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\jui\DatePicker;
use kartik\select2\Select2;
use common\components\helpers\CustomViewHelper;
use yii\helpers\Url;
/* @var $this yii\web\View */
/* @var $model common\models\Payments */
/* @var $form yii\widgets\ActiveForm */
/* @var $result array */
?>

<div>
    <p>Будут удалены платежи:</p>
    <?php foreach($result['payments'] as $item){?>
        <a href="<?=$item['url']?>"><?=$item['id']?></a>
    <?}?>
    <br>
    <br>
    <?php if(!empty($result['enrolls'])){?>
        <p>Будут удалены зачисления:</p>
        <?php foreach($result['enrolls'] as $item){?>
            <a href="<?=$item['url']?>"><?=$item['id']?></a>
        <?}?>
        <br>
        <br>
    <?}?>
    <?php if(!empty($result['enrolls_req'])){?>
        <p>Будут удалены запросы на зачисления:</p>
        <?php foreach($result['enrolls_req'] as $item){?>
            <a href="<?=$item['url']?>"><?=$item['id']?></a>
        <?}?>
        <br>
        <br>
    <?}?>
    <?php if(!empty($result['prom_pays'])){?>
        <p>Будут удалены данные для обещенного платежа:</p>
        <?php foreach($result['prom_pays'] as $item){?>
            <a href="<?=$item['url']?>"><?=$item['id']?></a>
        <?}?>
        <br>
        <br>
    <?}?>
    <?php if(!empty($result['pur_hist'])){?>
        <p><b>Также будут удалены данные о зачслениях для партнеров!</b></p>
        <br>
        <br>
    <?}?>
</div>
