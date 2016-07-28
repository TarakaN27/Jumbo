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
        'css/craftpip-jquery-confirm/css/jquery-confirm.css'
    ];
    public $js = [
        'js/wm_app/app.js',
        'js/script.js',
        'js/gauge/gauge.min.js',
        'js/nicescroll/jquery.nicescroll.min.js',
        'js/progressbar/bootstrap-progressbar.min.js',
        'js/icheck/icheck.min.js',
        'js/custom.js',
        'js/notify/pnotify.core.js',
        'js/notify/pnotify.buttons.js',
        'js/notify/pnotify.nonblock.js',
        'js/wm_app/custom_notify.js',
        'js/craftpip-jquery-confirm/js/jquery-confirm.js',
        'js/accounting/accounting.min.js',
        //'js/jquery-confirm/jquery.confirm.min.js'
        //'js/tooltips/toltips.js'
        //'js/socket_io/socket.io.js',
        //'js/wm_app/notification.js'
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}
