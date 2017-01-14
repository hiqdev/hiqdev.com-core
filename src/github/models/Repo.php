<?php
/**
 * hiqdev.com site
 *
 * @link      https://github.com/hiqdev/hiqdev.com-core
 * @package   hiqdev.com-core
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2016-2017, HiQDev (http://hiqdev.com/)
 */

namespace hiqdev\com\github\models;

class Repo extends \hiqdev\hiart\ActiveRecord
{
    public function rules()
    {
        return [
            ['id', 'integer'],
            ['name', 'string'],
        ];
    }
}

