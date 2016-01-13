<?php
return [
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=yii2advanced',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8',
            'tablePrefix' => 'wm_'
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'viewPath' => '@common/mail',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
            'cachePath' => '@backend/runtime/cache'
        ],
        'yadirect' => [
            'class' => 'common\components\yandexDirect\YADirect',
            'token' => '3e2c464c9f104a0e8ba3bee6357cde6d', //token yandex для авторизации в yandex
            'login' => 'motuzdev2', // логин аккаунта с доступом
            'useSandbox' => TRUE,   // использовать ли песочницу для запросов
            'masterToken' => 'JGfT4DkoXc3x88su', //мастер тоукен для финансовых операций
            'contractID' => '11111/00'   //номер кредитного договора
        ],
        'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => 'localhost',
            'port' => 6379,
            'database' => 2,
        ],
    ],
];
