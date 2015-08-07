<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 06.08.15
 */

namespace common\components\widgets\moneyMaskInputWidget\assets;



use yii\web\AssetBundle;

class MoneyMaskInputAssets extends AssetBundle{
    public
        $css = [

    ],
        $js = [

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