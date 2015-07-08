<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'app-frontend',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'name' => 'Front wmc',
    'controllerNamespace' => 'frontend\controllers',
    'components' => [
        'request' => [
            'baseUrl' => '',
            'csrfParam' => '_frontendCSRF',
            'csrfCookie' => [
                'httpOnly' => true,
                //    'path' => '/',
            ],

        ],
        'session' => [
            'name' => 'FRONTENDSESSID',
            'cookieParams' => [
                //   'path' => '/',
            ],
        ],
        'user' => [
            'identityClass' => 'common\models\CUser',
            'enableAutoLogin' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
        ],
    ],
    'params' => $params,
];
