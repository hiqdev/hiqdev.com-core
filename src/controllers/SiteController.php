<?php
/**
 * hiqdev.com site
 *
 * @link      https://github.com/hiqdev/hiqdev.com-core
 * @package   hiqdev.com-core
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2016-2017, HiQDev (http://hiqdev.com/)
 */

namespace hiqdev\com\controllers;

use hiart\github\models\Repo;
use Yii;

class SiteController extends \hisite\controllers\SiteController
{
    public function actionTest()
    {
        #$a = Yii::$app->github->get('orgs/hiqdev/repos');
        $a = Repo::find()->where(['organization' => 'hiqdev'])->all();
        var_dump($a);
        die();
    }
}
