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
    'defaultRoute' => 'pages/render/index',
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
        ],
    ],
    'components' => [
        'urlManager' => [
            'rules' => [
                'packages/<package:[.a-z0-9-]+>' => 'site/redirect',
                'packages/<package:[.a-z0-9-]+>/<x:X?>' => 'site/package',
                '<page:.*>' => 'pages/render/index',
            ],
        ],
        'themeManager' => [
            'pathMap' => [
                '$themedViewPaths' => ['@hiqdev/com/views'],
            ],
            'assets' => [
                \hiqdev\com\Asset::class,
            ],
        ],
    ],
];
