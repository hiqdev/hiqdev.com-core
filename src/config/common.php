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
    ],
];
