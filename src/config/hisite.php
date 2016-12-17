<?php
/**
 * hiqdev.com site
 *
 * @link      https://github.com/hiqdev/hiqdev.com-core
 * @package   hiqdev.com-core
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2014-2016, HiQDev (http://hiqdev.com/)
 */

return [
    'id' => 'hiqdev.com',
    'name' => 'HiQDev',
    'components' => [
        'themeManager' => [
            'pathMap' => [
                '$themedViewPaths' => ['@hiqdev/com/views'],
            ],
        ],
        'menuManager' => [
            'items' => [
                'main' => \hiqdev\com\menus\MainMenu::class,
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
    ],
];
