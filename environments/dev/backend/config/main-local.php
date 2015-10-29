<?php

$config = [
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => '3487r94t7t1cyfkjhg;sdufqpwmuyv34y[9023u298xO*Y',
        ],
    ],
];

if (!YII_ENV_TEST) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = 'yii\debug\Module';

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class'=>'yii\gii\Module',
        'generators' => [
            'crud' => [
                'class' => 'yii\gii\generators\crud\Generator',
                'templates' => [
                    'wmcorp_admin' => '@common/components/custom-gii-crud-template/admin' //собственный шаблон. для генерации использовать его
                ]
            ]
        ],
        'allowedIPs'=>['127.0.0.1','192.168.1.*'],
    ];
}

return $config;
