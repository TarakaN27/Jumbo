<?php
return [
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'language' => 'ru_RU',
    'sourceLanguage' => 'en_US',
    'version' => '1.0',
    'name' => 'Jumbo webmart',
    'timeZone'=> 'Europe/Minsk',
    'components' => [
        'config' => [
            'class' => 'common\components\config\Config'
        ],
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
                        'app/units' => 'units.php',
                        'app/documents' => 'documents.php',
                        'app/crm' => 'crm.php',
                        'app/config' => 'config.php',
                        'app/dialogs' => 'dialogs.php',
                        'app/bonus' => 'bonus.php'
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
            'dateFormat' => 'php:d.m.Y',
            'datetimeFormat' => 'php:d.m.Y H:i',
            'thousandSeparator' => ' ',
            'decimalSeparator' => ',',
            'timeZone' => 'Europe/Minsk',
            'defaultTimeZone' => 'Europe/Minsk',
            'decimalSeparator'=>','
        ],
    ],
];
