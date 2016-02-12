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
    'only_jurist' => [
        'type' => 2,
        'description' => 'Только для юриста',
    ],
    'only_e_marketer' => [
        'type' => 2,
        'description' => 'Только для емаил маркетолага',
    ],
    'user' => [
        'type' => 1,
        'description' => 'Пользователь',
        'ruleName' => 'userRole',
    ],
    'jurist' => [
        'type' => 1,
        'description' => 'Юрист',
        'ruleName' => 'userRole',
        'children' => [
            'only_jurist',
            'user',
            'forAll',
        ],
    ],
    'e_marketer' => [
        'type' => 1,
        'description' => 'Емаил маркетолог',
        'ruleName' => 'userRole',
        'children' => [
            'only_e_marketer',
            'user',
            'forAll',
        ],
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
