<?php

$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'app-api',
    'basePath' => dirname(__DIR__),    
    'bootstrap' => ['log'],
    'modules' => [
        'v1' => [
            //'basePath' => '@app/modules/v1',
            'class' => 'api\modules\v1\V1Module'
        ]
    ],
    'components' => [
        'user' => [
            'identityClass' => 'common\models\CUser',
            'enableAutoLogin' => false,
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
        'request' => [
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ]
        ],
        'response' => [
            'format' => yii\web\Response::FORMAT_JSON,
            'charset' => 'UTF-8',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => true,
            'showScriptName' => false,
            'rules' => [
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => ['v1/service' => 'v1/service'],
                    'except' => ['delete', 'create', 'update'], //запрещаем действия
                    'extraPatterns' => [    //дополнительные экшены
                        'POST get-services' => 'get-services', // 'xxxxx' refers to 'actionXxxxx'
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => ['v1/technical' => 'v1/technical'],
                    'extraPatterns' => [    //дополнительные экшены
                        'GET ping' => 'ping', // 'xxxxx' refers to 'actionXxxxx'
                    ],
                ],
            ],
        ]
    ],
    'params' => $params,
];



