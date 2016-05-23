<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 23.5.16
 * Time: 10.51
 */

namespace common\components\helpers;


class CustomViewHelper
{
    /**
     * @param $path
     * @param $view
     * @param array $additionDependency
     * @param null $key
     * @return mixed
     */
    public static function registerJsFileWithDependency($path,$view,array $additionDependency = [],$key = NULL)
    {
        $arDepends = [
            'yii\web\JqueryAsset',
            'yii\web\YiiAsset',
            'yii\bootstrap\BootstrapPluginAsset',
        ];

        foreach ($additionDependency as $dep)
        {
            $arDepends [] = $dep;
        }
        $view->registerJsFile($path,[
            'depends' => $arDepends
        ],$key);
        return TRUE;
    }
    
}