<?php
/**
 * hiqdev.com site
 *
 * @link      https://github.com/hiqdev/hiqdev.com-core
 * @package   hiqdev.com-core
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2016-2017, HiQDev (http://hiqdev.com/)
 */

namespace hiqdev\com;

use yii\web\AssetBundle;

/**
 * Site additional assets.
 */
class Asset extends AssetBundle
{
    /**
     * {@inheritdoc}
     */
    public $sourcePath = '@hiqdev/com/assets';

    /**
     * {@inheritdoc}
     */
    public $css = [
        'css/style.css',
    ];
}
