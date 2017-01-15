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

use Yii;

class ActiveRecord extends \hiqdev\hiart\ActiveRecord
{
    public static function getDb()
    {
        return Yii::$app->get('github');
    }
}

