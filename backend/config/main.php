<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'app-backend',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'backend\controllers',
    'bootstrap' => ['log'],
    'modules' => [
        'users' => [
            'class' => 'backend\modules\users\Module',
        ],
        'services' => [
            'class' => 'backend\modules\services\Module',
        ],
        'bookkeeping' => [
            'class' => 'backend\modules\bookkeeping\Module',
        ],
        'reports' => [
            'class' => 'backend\modules\reports\Module',
        ],
        'messenger' => [
            'class' => 'backend\modules\messenger\Module',
        ],
    ],
    'components' => [

        //------------------разделение сессий, csrf -ключей, пользоватлей
        'request' => [
            'csrfParam' => '_backendCSRF',
            'baseUrl' => '/service',
            'csrfCookie' => [
                'httpOnly' => true,
                'path' => '/service',
            ],
        ],
        'session' => [
            'name' => 'BACKENDSESSID',
            'cookieParams' => [
                'path' => '/service',
            ],
        ],
        'user' => [
            'identityClass' => 'backend\models\BUser',
            'enableAutoLogin' => true,
            'identityCookie' => [
                'name' => '_backendEndUser', // unique for frontend
                'path' => '/service' // correct path for backend app.
            ]
        ],
        //роли
        'authManager' => [
            'class' => 'yii\rbac\PhpManager',
            'defaultRoles' => ['user','moder','bookkeeper','admin','superadmin'], //здесь прописываем роли
            //зададим куда будут сохраняться наши файлы конфигураций RBAC
            'itemFile' => '@backend/components/rbac/items.php',
            'assignmentFile' => '@backend/components/rbac/assignments.php',
            'ruleFile' => '@backend/components/rbac/rules.php'
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
    ],
    'params' => $params,
];
