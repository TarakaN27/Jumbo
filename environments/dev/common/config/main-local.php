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
        'mailer' => [                               //маилер для внутренних писем
            'class' => 'yii\swiftmailer\Mailer',
            'viewPath' => '@common/mail',
                // send all mails to a file by default. You have to set
                // 'useFileTransport' to false and configure a transport
                // for the mailer to send real emails.
            'useFileTransport' => true,
                /*
                'useFileTransport' => false,
                'transport' => [
                    'class' => 'Swift_SmtpTransport',
                    'host' => 'smtp.gmail.com',
                    'username' => 'jumbo.webmart@gmail.com',
                    'password' => 'k8JfmgHp',
                    'port' => '587',
                    'encryption' => 'tls',
                ],
                */

        ],
        'salesMailer' => [                      //маилер для отправки актов
            'class' => 'yii\swiftmailer\Mailer',
            'viewPath' => '@common/mail',
                // send all mails to a file by default. You have to set
                // 'useFileTransport' to false and configure a transport
                // for the mailer to send real emails.
            'useFileTransport' => true,
                /*
                'useFileTransport' => false,
                'transport' => [
                    'class' => 'Swift_SmtpTransport',
                    'host' => 'smtp.yandex.ru',
                    'username' => 'sales@webmart.by',
                    'password' => 'web-mart',
                    'port' => '465',
                    'encryption' => 'ssl',
                ],
                */
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
        'rabbit' => [
            'class' => 'common\components\rabbitmq\Rabbit',
            'host' => '127.0.0.1',
            'port' => 5672,
            'login' => 'guest',
            'password' =>'guest',
            'vhost' => '/'
        ]
    ],
];
