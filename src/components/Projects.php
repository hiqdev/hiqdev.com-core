<?php

namespace hiqdev\com\components;

use Symfony\Component\Yaml\Yaml;

class Projects
{
    public function getAll()
    {
        return Yaml::parse(file_get_contents(__DIR__ . '/projects.yml'));
    }
}
