<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 6.7.16
 * Time: 11.26
 */
use yii\helpers\Html;

$head = Html::tag('th',Yii::t('app/bonus','Legal person'));
$head.= Html::tag('th',Yii::t('app/bonus','Deduct VAT'));
$head.= Html::tag('th',Yii::t('app/bonus','Detail'));
$str = Html::tag('thead',Html::tag('tr',$head));
unset($head);
foreach($arLegal as $key=>$value)
{
    $bVal = isset($model[$key],$model[$key]['deduct']) ? $model[$key]['deduct'] : 0;
    $tmp = Html::tag('th',$value);
    $tmp.= Html::tag('td',Yii::$app->formatter->asBoolean($bVal));$tmpTable = '';
    $tmpTable = '';
    if($bVal)
    {
        $tmpBody = '';
        $head = Html::tag('th','');
        $head.= Html::tag('th',Yii::t('app/bonus','deduct tax'));
        $head.= Html::tag('th',Yii::t('app/bonus','Custom tax'));
        $tmpHead = Html::tag('thead',Html::tag('tr',$head));
        $tmpTd = Html::tag('td',Yii::t('app/bonus','Resident'));
        $tmpTd.= Html::tag('td',isset($model[$key]) && isset($model[$key]['res']) ? Yii::$app->formatter->asBoolean($model[$key]['res']) : '');
        $tmpTd.= Html::tag('td',isset($model[$key]) && isset($model[$key]['res_tax']) ? $model[$key]['res_tax'] : '');
        $tmpBody.=Html::tag('tr',$tmpTd);
        $tmpTd = Html::tag('td',Yii::t('app/bonus','Not resident'));
        $tmpTd.= Html::tag('td',isset($model[$key]) && isset($model[$key]['not_res']) ? Yii::$app->formatter->asBoolean($model[$key]['not_res']) : '');
        $tmpTd.= Html::tag('td',isset($model[$key]) && isset($model[$key]['not_res_tax']) ? $model[$key]['not_res_tax'] : '');
        $tmpBody.=Html::tag('tr',$tmpTd);
        $tmpTable = Html::tag('table',Html::tag('tbody',$tmpHead.$tmpBody),['class' => 'table table-bordered']);
    }
    $tmp.=Html::tag('td',$tmpTable);
    $str.=Html::tag('tr',$tmp);
}
echo Html::tag('table',Html::tag('tbody',$str),['class' => 'table table-bordered']);
?>