<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace backend\assets;

use yii\web\AssetBundle;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
       'css/normalize.css',
       'css/custom.css',
       'fonts/css/font-awesome.min.css',
       'css/animate.min.css',
       'css/icheck/flat/green.css',
       'css/site.css',
    ];
    public $js = [
        'js/gauge/gauge.min.js',
        'js/nicescroll/jquery.nicescroll.min.js',
        'js/progressbar/bootstrap-progressbar.min.js',
        'js/icheck/icheck.min.js',
        'js/custom.js',

    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}
