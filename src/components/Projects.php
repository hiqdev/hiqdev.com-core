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

class Projects extends \yii\base\Component
{
    public function getPackages()
    {
        $projects = $this->getAll();
        $packages = [];

        return $packages;
    }

    public function getAll()
    {
        return $this->raw['projects'];
    }

    protected $_raw;

    public function getRaw()
    {
        if ($this->_raw === null) {
            $this->_raw = $this->prepare(Yaml::parse(file_get_contents(__DIR__ . '/projects.yml')));
        }

        return $this->_raw;
    }

    protected function prepare(array $data)
    {
        foreach ($data['projects'] as $name => &$project) {
            $project['package']     = $project['package'] ?: $name;
            $project['vendor']      = $project['vendor'] ?: 'hiqdev';
            $project['fullName']    = $project['fullName'] ?: $project['vendor'] . '/' . $project['package'];
        }

        return $data;
    }
}
