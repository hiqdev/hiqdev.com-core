<?php
/**
 * hiqdev.com site
 *
 * @link      https://github.com/hiqdev/hiqdev.com-core
 * @package   hiqdev.com-core
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2016-2017, HiQDev (http://hiqdev.com/)
 */

namespace hiqdev\com\menus;

use Yii;

/**
 * Main menu.
 *
 * @author Andrii Vasyliev <sol@hiqdev.com>
 */
class MainMenu extends \hiqdev\yii2\menus\Menu
{
    public function items()
    {
        return [
            'news' => [
                'label' => Yii::t('hiqdev:com', 'News'),
                'url' => ['/pages/render/index', 'page' => 'index'],
            ],
            'about' => [
                'label' => Yii::t('hiqdev:com', 'About'),
                'url' => ['/pages/render/index', 'page' => 'about'],
            ],
            'packages' => [
                'label' => Yii::t('hiqdev:com', 'Packages'),
                'url' => ['/pages/render/index', 'page' => 'packages'],
            ],
            'projects' => [
                'label' => Yii::t('hiqdev:com', 'Projects'),
                'url' => ['/pages/render/index', 'page' => 'projects'],
            ],
        ];
    }
}
