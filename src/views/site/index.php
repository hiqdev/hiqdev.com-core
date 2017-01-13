<?php

use hiqdev\themes\hyde\widgets\LinkPager;
use yii\helpers\Html;

$this->title = Yii::$app->name;

$dataProvider = new \yii\data\ArrayDataProvider([
    'allModels' => [
        ['title' => 123, 'post-date' => '2017-01-12'],
        ['title' => 456, 'post-date' => '2017-01-12'],
        ['title' => 123, 'post-date' => '2017-01-12'],
        ['title' => 456, 'post-date' => '2017-01-12'],
        ['title' => 123, 'post-date' => '2017-01-12'],
        ['title' => 456, 'post-date' => '2017-01-12'],
        ['title' => 123, 'post-date' => '2017-01-12'],
        ['title' => 456, 'post-date' => '2017-01-12'],
        ['title' => 123, 'post-date' => '2017-01-12'],
        ['title' => 456, 'post-date' => '2017-01-12'],
        ['title' => 123, 'post-date' => '2017-01-12'],
        ['title' => 456, 'post-date' => '2017-01-12'],
        ['title' => 123, 'post-date' => '2017-01-12'],
        ['title' => 456, 'post-date' => '2017-01-12'],
        ['title' => 123, 'post-date' => '2017-01-12'],
        ['title' => 456, 'post-date' => '2017-01-12'],
        ['title' => 123, 'post-date' => '2017-01-12'],
        ['title' => 456, 'post-date' => '2017-01-12'],
        ['title' => 123, 'post-date' => '2017-01-12'],
        ['title' => 456, 'post-date' => '2017-01-12'],
        ['title' => 123, 'post-date' => '2017-01-12'],
        ['title' => 456, 'post-date' => '2017-01-12'],
        ['title' => 123, 'post-date' => '2017-01-12'],
        ['title' => 456, 'post-date' => '2017-01-12'],
        ['title' => 123, 'post-date' => '2017-01-12'],
        ['title' => 456, 'post-date' => '2017-01-12'],
    ],
]);

?>

<?= \yii\widgets\ListView::widget([
    'layout' => "{items}\n{pager}",
    'options' => [
        'class' => 'posts',
    ],
    'itemOptions' => [
        'class' => 'post',
    ],
    'dataProvider' => $dataProvider,
    'pager' => [
        'class' => LinkPager::class,
        'prevPageLabel' => Yii::t('hiqdev:com', 'Newer'),
        'nextPageLabel' => Yii::t('hiqdev:com', 'Older'),
    ],
    'itemView' => function ($model, $key, $item, $widget) {
        $out = Html::tag('h1', $model['title'], ['class' => 'post-title']);
        $out .= Html::tag('span', Yii::$app->formatter->asDate($model['post-date']), ['class' => 'post-date']);

        return $out;
    },
]) ?>
