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

use cebe\markdown\GithubMarkdown;
use hiart\github\models\Repo;
use Yii;

class SiteController extends \hisite\controllers\SiteController
{
    public function actionTest()
    {
        die('TEST');
    }

    public function actionRedirect()
    {
        return $this->redirect(Yii::$app->request->getUrl() . '/');
    }

    public function actionPackage($package)
    {
        $this->layout = 'package';

        Yii::$app->view->title = $package;
        Yii::$app->view->params += [
            'package'   => $package,
            'fullName'  => 'hiqdev/' . $package,
        ];
        $parser = new GithubMarkdown();
        $path = Yii::getAlias("@hiqdev/com/pages/packages/$package/README.md");

        return $this->render('package', [
            'readme' => $parser->parse(file_get_contents($path)),
        ]);
    }
}
