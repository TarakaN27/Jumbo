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
    'only_partner_manager' => [
        'type' => 2,
        'description' => 'Только для менеджера по партнерам',
    ],
	'only_sale' => [
        'type' => 2,
        'description' => 'Только для продавца',
    ],
    'only_teamlead' => [
        'type' => 2,
        'description' => 'Только для тимлида',
    ],
    'only_hr' => [
        'type' => 2,
        'description' => 'Только для HR',
    ],
    'user' => [
        'type' => 1,
        'description' => 'Специалист',
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
	'sale' => [
        'type' => 1,
        'description' => 'Продавец',
        'ruleName' => 'userRole',
        'children' => [
            'only_sale',
            'user',
            'only_manager',
            'forAll',
        ],
    ],
    'partner_manager' => [
        'type' => 1,
        'description' => 'Менеджер по работе с партнерами',
        'ruleName' => 'userRole',
        'children' => [
            'only_partner_manager',
            'user',
            'moder',
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
    'teamlead' => [
        'type' => 1,
        'description' => 'Тимлид PPC',
        'ruleName' => 'userRole',
        'children' => [
            'moder',
            'only_manager',
			'only_teamlead',
            'forAll',
        ],
    ],
	'teamlead_seo' => [
        'type' => 1,
        'description' => 'Тимлид SEO',
        'ruleName' => 'userRole',
        'children' => [
            'teamlead'
        ],
    ],
	'teamlead_acc' => [
        'type' => 1,
        'description' => 'Тим лид аккаунтинга',
        'ruleName' => 'userRole',
        'children' => [
            'adminRights',
			'admin',
            'forAll',
        ],
    ],
	'teamlead_sale' => [
        'type' => 1,
        'description' => 'Тим лид продаж',
        'ruleName' => 'userRole',
        'children' => [
            'only_sale',
			'sale',
			'only_manager',
            'user',
            'forAll',
        ],
    ],
    'hr' => [
        'type' => 1,
        'description' => 'HR',
        'ruleName' => 'userRole',
        'children' => [
            'user',
            'forAll',
            'only_hr',
        ],
    ],
];
