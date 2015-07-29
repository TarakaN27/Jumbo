<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 29.07.15
 */

namespace common\components\multiSelect\assets;


use yii\web\AssetBundle;

class MultiSelectAssets extends AssetBundle{

    public
        $css = [
            'css/multi-select.css',
        ],
        $js = [
            'js/jquery.multi-select.js'
        ],
        $depends = [
            'yii\web\JqueryAsset',
            'yii\web\YiiAsset',
            'yii\bootstrap\BootstrapAsset',
        ],
        $publishOptions = [
            'forceCopy' => true
        ];

    public function init()
    {
        $this->sourcePath = __DIR__;
        parent::init();
    }

} 