<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 06.08.15
 */

namespace common\components\widgets\maskInputWidget\assets;


use yii\web\AssetBundle;

class MaskInputAssets extends AssetBundle{

    public
        $css = [
        //'css/multi-select.css',
    ],
        $js = [
        'js/jquery.mask.min.js'
    ],
        $depends = [
        'yii\web\JqueryAsset',
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
    //    $publishOptions = [
    //    'forceCopy' => true
    //];

    public function init()
    {
        $this->sourcePath = __DIR__;
        parent::init();
    }

} 