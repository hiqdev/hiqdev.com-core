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
            'normalizer' => [
                'class' => \yii\web\UrlNormalizer::class,
            ],
            'rules' => [
                /* [
                    'class' => \hisite\components\RedirectRule::class,
                    'pattern' => 'hiqdev/<package:[.a-z0-9-]+><page:.*>',
                    'route' => 'packages/<package><page>',
                ], */
                /*
                'redirects' => [
                    'pages/posts'   => '',
                    'projects' => 'pages/projects',
                    'packages' => 'pages/packages',
                    'hiqdev/<page:.*>' => 'packages/<page>',
                ],
                */
                'hiqdev/<package:[.a-z0-9-]+><page:.*>' => 'site/hiqdev',
                [
                    'pattern' => 'packages/<package:[.a-zA-Z0-9-]+>',
                    'route' => 'site/package',
                    'suffix' => '/',
                ],
                '' => 'pages/render/index',
                'draft/<x:.*>' => 'pages/render/index',
                'packages/<x:.*>' => 'pages/render/index',
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
