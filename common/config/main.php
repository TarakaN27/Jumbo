<?php
return [
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'language' => 'ru_RU',
    'sourceLanguage' => 'en_US',
    'version' => '1.0',
    'name' => 'Webmart Group corp',
    'timeZone'=> 'Europe/Minsk',
    'components' => [
       // 'cache' => [
       //     'class' => 'yii\caching\FileCache',
       // ],
        'i18n' => [
            'translations' => [
                'app*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@common/messages',
                    'sourceLanguage' => 'en_US',
                    'fileMap' => [
                       // 'app' => 'app.php',
                        'app/users' => 'users.php',
                        'app/services' => 'services.php',
                        'app/common' => 'common.php',
                        'app/book' => 'bookkeeping.php',
                        'app/reports' => 'reports.php',
                        'app/msg' => 'messenger.php',
                        'app/units' => 'units.php'
                    ]
                ]
            ]
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
        ],
        'formatter' => [
            'class' => 'yii\i18n\Formatter',
            'dateFormat' => 'medium',
            'datetimeFormat' => 'medium'
        ],
    ],
];
