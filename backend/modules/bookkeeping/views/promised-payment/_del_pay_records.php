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
    <?if(isset($result['is_paid'])){?>
        <p>Обещанный платеж полностью погашен и не подлежит удалению</p>
    <?}?>
    <?if(isset($result['promisePaymentForDelete']) && $result['promisePaymentForDelete']){?>
        <p>Вы действительно хотите удалить обещанный платеж <?=$result['promisePaymentForDelete'][0]->id?>?</p>
    <?}?>
    <?if(isset($result['promisePaymentForUpdate']) && $result['promisePaymentForUpdate']){?>
        <p>Обещанный платеж будет перевед в статус погашенный, и кол-во единиц станет - <?=$result['promisePaymentForUpdate'][0]->amount?></p>
    <?}?>
    <?if(isset($result['enrollsForModification']) && $result['enrollsForModification']){?>
        <p>Зачисление <a target="_blank" href="<?=Url::to(['/bookkeeping/enrolls/view', 'id'=> $result['enrollsForModification'][0]->id])?>"><?=$result['enrollsForModification'][0]->id?></a> будет изменено, новое кол-во единиц - <?=$result['enrollsForModification'][0]->amount?></p>
    <?}?>
    <?if(isset($result['enrollsForDelete']) && $result['enrollsForDelete']){?>
        <p>Зачисления <?foreach($result['enrollsForDelete'] as $item){?> <a target="_blank" href="<?=Url::to(['/bookkeeping/enrolls/view', 'id'=> $item->id])?>"><?=$item->id?></a> <?}?> будет удалено</p>
    <?}?>
    <?if(isset($result['enrollRequestForDelete']) && $result['enrollRequestForDelete']){?>
        <p>Запрос на зачисления <?foreach($result['enrollRequestForDelete'] as $item){?> <a target="_blank" href="<?=Url::to(['/bookkeeping/enrollment-request/process', 'id'=> $item->id])?>"><?=$item->id?></a> <?}?> будет удалено</p>
    <?}?>
</div>
