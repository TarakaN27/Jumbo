<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 7.4.16
 * Time: 15.27
 */
use yii\helpers\Html;
$table = Html::tag('table',NULL,['class' => 'table']);
$btn = Html::button(Yii::t('app/common','Load more'),[
    'class' => 'load-more hide btn btn-default'
]);
?>
<?=Html::tag('div',$table.$btn,[
    'class' => 'crm-log-widget',
    'data-loaded' => 0,
    'data-entity' => $entityName,
    'data-item' => $itemID,
    'data-autoinit' => $autoInit ? 1 : 0,
    'data-clevent' => $clickEventsItem,
    'data-page' => 0,
    'data-url' => $url
])?>

