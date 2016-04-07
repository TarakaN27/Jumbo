<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 7.4.16
 * Time: 16.25
 */
use yii\helpers\Html;

    $tr= NULL;

    foreach ($models as $model)
    {
        $td = '';
        $td.= Html::tag('td',$model->description);
        $td.= Html::tag('td',Yii::$app->formatter->asDatetime($model->created_at));
        $tr.= Html::tag('tr',$td);
    }
    echo $tr;
?>
