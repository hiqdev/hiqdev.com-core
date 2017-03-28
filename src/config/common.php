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
    'components' => [
        'mailer' => [
            'class' => \yii\swiftmailer\Mailer::class,
        ],
        'i18n' => [
            'class' => \yii\i18n\I18N::class,
            'translations' => [
                'hiqdev:com' => [
                    'class' => \yii\i18n\PhpMessageSource::class,
                    'basePath' => '@hiqdev/com/messages',
                ],
            ],
        ],
        'projects' => [
            'class' => \hiqdev\com\components\Projects::class,
        ],
        'packages' => [
            'class' => \hiqdev\com\components\Packages::class,
        ],
         'sentry' => [
            'class' => \mito\sentry\Component::class,
            'dsn' => isset($params['sentry.dsn']) ? $params['sentry.dsn'] : '',
            'environment' => YII_ENV,
            'jsNotifier' => true,
            'jsOptions' => [
                'whitelistUrls' => [
                    'https://dev.hiqdev.com',
                    'https://hiqdev.com',
                ],
            ],
        ],
        'log' => [
            'targets' => [
                [
                    'class' => \mito\sentry\Target::class,
                    'levels' => ['error'],
                ],
            ],
        ],
    ],
];
