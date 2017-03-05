<?php

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
