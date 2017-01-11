<?php

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
                'url' => ['/'],
            ],
            'about' => [
                'label' => Yii::t('hiqdev:com', 'About'),
                'url' => ['/site/about'],
            ],
            'packages' => [
                'label' => Yii::t('hiqdev:com', 'Packages'),
                'url' => ['/packages'],
            ],
            'projects' => [
                'label' => Yii::t('hiqdev:com', 'Projects'),
                'url' => '/projects',
            ],
        ];
    }
}
