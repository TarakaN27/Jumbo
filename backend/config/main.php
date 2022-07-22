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
        'helpers' => [ //модуль с помощниками
            'class' => 'backend\modules\helpers\Module',
        ],
        'units' => [
            'class' => 'backend\modules\units\Module',
        ],
        'documents' => [
            'class' => 'backend\modules\documents\Module',
        ],
        'config' => [
            'class' => 'backend\modules\config\Module',
        ],
        'crm' => [
            'class' => 'app\modules\crm\Module',
        ],
        'bonus' => [
            'class' => 'backend\modules\bonus\Module',
        ],
        'partners' => [
            'class' => 'backend\modules\partners\Module',
        ],
        'attachments' => [
            //'class' => nemmo\attachments\Module::className(),
            'class' => \common\components\customComponents\nemofileattachments\NemoModuleCustom::className(),
            'tempPath' => '@backend/uploads/temp',
            'storePath' => '@backend/uploads/store',
            'rules' => [ // Rules according to the FileValidator
                'maxFiles' => 10, // Allow to upload maximum 3 files, default to 3
                //'mimeTypes' => 'image/png', // Only png images
                'maxSize' => 20*1024 * 1024 // 1 MB
            ],
            'tableName' => '{{%attachments}}' // Optional, default to 'attach_file'
        ]
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
			'enableCsrfValidation'=>false,
        ],
        'session' => [
            'name' => 'BACKENDSESSID',
            'cookieParams' => [
                'path' => '/service',
            ],
        ],
        'user' => [
            'class' => 'backend\components\CustomUser',
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
            'defaultRoles' => ['user','jurist','e_marketer','moder','sale','bookkeeper','admin','superadmin','partner_manager','teamlead','teamlead_seo','teamlead_acc','teamlead_sale','hr'], //здесь прописываем роли
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
                    'levels' => YII_DEBUG ? ['info','error', 'warning'] : ['error', 'warning'],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['info'],
                    'logVars' => [],
                    'categories' => ['pushUserBehaviors'],
                    'logFile' => '@app/runtime/logs/users/users_behaviors.log',
                    'maxFileSize' => 1024 * 2,
                    'maxLogFiles' => 50,
                    'enabled' => TRUE           //Установить FALSE если не нужно логирование действий с моделями
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        /*
        'view' => [
            'class' => '\rmrevin\yii\minify\View',
            'enableMinify' => false,
            'web_path' => '@web', // path alias to web base
            'base_path' => '@webroot', // path alias to web base
            'minify_path' => '@webroot/minify', // path alias to save minify result
            'js_position' => [ \yii\web\View::POS_END ], // positions of js files to be minified
            'force_charset' => 'UTF-8', // charset forcibly assign, otherwise will use all of the files found charset
            'expand_imports' => true, // whether to change @import on content
            'compress_output' => true, // compress result html page
            'compress_options' => ['extra' => true], // options for compress
        ]
        */
    ],
    'params' => $params,
];
