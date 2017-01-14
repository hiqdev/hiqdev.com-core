<?php
/**
 * hiqdev.com site
 *
 * @link      https://github.com/hiqdev/hiqdev.com-core
 * @package   hiqdev.com-core
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2016-2017, HiQDev (http://hiqdev.com/)
 */

return [
    'id' => 'hiqdev.com',
    'name' => 'HiQDev',
    'controllerNamespace' => 'hiqdev\\com\\controllers',
    'container' => [
        'definitions' => [
            \hiqdev\thememanager\menus\AbstractMainMenu::class => \hiqdev\com\menus\MainMenu::class,
        ],
    ],
    'modules' => [
        'pages' => [
            'storage' => [
                'class' => \creocoder\flysystem\LocalFilesystem::class,
                'path' => '@hiqdev/com/pages',
            ],
        ]
    ],
    'components' => [
        'themeManager' => [
            'pathMap' => [
                '$themedViewPaths' => ['@hiqdev/com/views'],
            ],
        ],
        'i18n' => [
            'translations' => [
                'hiqdev:com' => [
                    'class' => \yii\i18n\PhpMessageSource::class,
                    'basePath' => '@hiqdev/com/messages',
                ],
            ],
        ],
        'github' => [
            'class' => \hiqdev\com\github\Api::class,
            'config' => [
                'base_uri' => 'https://api.github.com',
            ],
        ],
    ],
];
