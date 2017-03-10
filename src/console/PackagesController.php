<?php
/**
 * hiqdev.com site
 *
 * @link      https://github.com/hiqdev/hiqdev.com-core
 * @package   hiqdev.com-core
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2016-2017, HiQDev (http://hiqdev.com/)
 */

namespace hiqdev\com\console;

use hiqdev\hiart\github\models\Repo;
use Yii;
use Symfony\Component\Yaml\Yaml;

class PackagesController extends \yii\console\Controller
{
    public function actionLoad()
    {
        $packages = [];
        $page = 1;
        do {
            $repos = Repo::find()->where([
                'organization' => 'hiqdev',
                'page' => $page,
            ])->all();
            $page++;
            foreach ($repos as $repo) {
                $packages[$repo->name] = [
                    'name'          => $repo->name,
                    'description'   => $repo->description,
                    'forks'         => $repo->forks_count,
                    'stars'         => $repo->stargazers_count,
                    'watchers'      => $repo->watchers_count,
                    'issues'        => $repo->open_issues_count,
                ];
            }
        } while ($repos && $page<10);

        $path = Yii::getAlias('@hiqdev/com/components/packages.yml');
        file_put_contents($path, Yaml::dump($packages));
    }
}
