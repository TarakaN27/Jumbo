<?php
return [
    'only_bookkeeper' => [
        'type' => 2,
        'description' => 'Только для бухгалтера',
    ],
    'adminRights' => [
        'type' => 2,
        'description' => 'Админские права',
    ],
    'superRights' => [
        'type' => 2,
        'description' => 'Права супер пользователя',
    ],
    'only_manager' => [
        'type' => 2,
        'description' => 'Только для менеджера',
    ],
    'forAll' => [
        'type' => 2,
        'description' => 'Права для всех',
    ],
    'user' => [
        'type' => 1,
        'description' => 'Пользователь',
        'ruleName' => 'userRole',
    ],
    'moder' => [
        'type' => 1,
        'description' => 'Модератор',
        'ruleName' => 'userRole',
        'children' => [
            'user',
            'only_manager',
            'forAll',
        ],
    ],
    'bookkeeper' => [
        'type' => 1,
        'description' => 'Бухгалтер',
        'ruleName' => 'userRole',
        'children' => [
            'user',
            'only_bookkeeper',
            'forAll',
        ],
    ],
    'admin' => [
        'type' => 1,
        'description' => 'Администратор',
        'ruleName' => 'userRole',
        'children' => [
            'user',
            'adminRights',
            'forAll',
        ],
    ],
    'superadmin' => [
        'type' => 1,
        'description' => 'Супер администратор',
        'ruleName' => 'userRole',
        'children' => [
            'admin',
            'superRights',
            'forAll',
        ],
    ],
];
