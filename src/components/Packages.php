<?php
/**
 * hiqdev.com site
 *
 * @link      https://github.com/hiqdev/hiqdev.com-core
 * @package   hiqdev.com-core
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2016-2017, HiQDev (http://hiqdev.com/)
 */

namespace hiqdev\com\components;

use Symfony\Component\Yaml\Yaml;

class Packages extends \yii\base\Component
{
    public function getAll()
    {
        return $this->raw;
    }

    protected $_raw;

    public function getPath()
    {
        return __DIR__ . '/packages.yml';
    }

    public function getRaw()
    {
        if ($this->_raw === null) {
            $this->_raw = $this->prepare(Yaml::parse(file_get_contents($this->getPath())));
        }

        return $this->_raw;
    }

    protected function prepare(array $data)
    {
        foreach ($data as $name => &$package) {
            $package['package']     = $package['package'] ?: $name;
            $package['vendor']      = $package['vendor'] ?: 'hiqdev';
            $package['fullName']    = $package['fullName'] ?: $package['vendor'] . '/' . $package['package'];
            $package['total']       = $package['stars'] + $package['watchers'] + $packages['forks'];
        }

        uasort($data, function ($a, $b) {
            return $b['total'] - $a['total'];
        });

        return $data;
    }
}
