<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 14.4.16
 * Time: 12.28
 */

namespace common\components\helpers;
use Yii;
use yii\helpers\Html;

class CustomHtmlHelper
{
    public static function dropDownSettings($arLinks,$title = '<i class="glyphicon glyphicon-cog"></i>')
    {
        $linksStr = '';
        foreach ($arLinks as $link) {
            $options = isset($link['options']) ? $link['options'] : [];
            $linksStr .= Html::tag('li', Html::a($link['title'], $link['href'], $options ));
        }
        $strHead = Html::a($title,NULL,[
            'data-toggle' => 'dropdown',
            'class' => 'link-btn-cursor dropdown-toggle',
            'type' => 'button',
            'aria-expanded' => 'false'
        ]);
        $linksStr = Html::tag('ul',$linksStr,['class' => 'dropdown-menu']);
        return Html::tag('div',$strHead.$linksStr,[
            'class' => 'btn-group'
        ]);

    }


}