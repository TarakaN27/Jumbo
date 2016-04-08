<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 7.4.16
 * Time: 15.24
 */

namespace common\components\widgets\crmLogWidget\assets;


use yii\web\AssetBundle;

class CrmLogAssets extends AssetBundle
{
    public
        $css = [

        ],
        $js = [
            'js/crm_log.js'
        ],
        $depends = [
            'yii\web\JqueryAsset',
            'yii\web\YiiAsset',
            'yii\bootstrap\BootstrapAsset'
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