---
title: Yii2 projects allternative organization
---

# Yii2 projects allternative organization

<img src="http://cdn.hiqdev.com/hiqdev/3dpuzzle.png" align="right"/>

How is it supposed to create Yii2 project now? Choose template project: basic or advanced, fork it then edit and commit right there. Wham! You've made copy-pasting! Your project and the template develop separately since now. You will not get fixes to the template. And your improvements that are specific for your tasks will not be accepted into `yii2-app-basic`. This the problem number one.

How is it supposed to evolve Yii2 project? Choose suitable extensions and plug them in with composer. Then you find example config for the extension in it's README and copy this example into your application config. Oops... You cook copypasta again! It can make troubles in many ways, e.g. in a big project many extensions can be used &mdash; application config becomes just huge and unreadable. This is the problem number two.

How are these probles related? You solve first with separating reusable code and turning it into an extension. And you've got second problem &mdash; extension needs config.

These problems become most acute for repeated projects when you have to deploy many/several similar projects with big/small changes. But removing copypasta and code reuse never hurt anyone.

I want to share my solution for outlined problems.

<habracut/>

## Plugins system

So here is the solution: use plugins system &mdash; from the very beginning create a project as a plugin, split project into plugins and assemble application config automatically from configs of plugins.

I have to slow down here and explain what do I call plugin. Yii2 supports extensions and they enable organization of reusable code and plug it in to a project with composer. But any simplest extension needs a config. And the framework doesn't help here much. Author of an extension has two options:

- describe desired config in the README file and propose programmer to copy-paste it;
- use [bootstrap](http://www.yiiframework.com/doc-2.0/guide-structure-extensions.html#bootstrapping-classes) in your extension that will put desired config into application config.

I've criticized first option above. Now I'll take second:

- bootstrap is run quite early, but `Application` object is already created and it's just too late to configure certain things;
- it is rather difficult to merge with config of already created application, you'll have to work not with a whole config bit in parts: components separately (and very non trivial), aliases separately, DI container, modules, params, `controllerMap`, ... (I tried &mdash; that's not going to work);
- bootstrap is not lazy, it is run on every application request and if you have many such bootstraps they will hurt performance.

After several iterations and tried different variants I've come to a radical solution &mdash; assemble config outside of application before it starts. Hmm, sounds easy and obvious, but best idea just doesn't come first. It turned out that it is most suitable to assemble config with a composer plugin it has convenient access to all information about project dependencies. This is how [composer-config-plugin](https://github.com/hiqdev/composer-config-plugin) was created.

## Composer Config Plugin

Composer-config-plugin works rather simply:

- обходит все зависимости проекта, находит в них описание конфигов плагинов в `extra` секции их `composer.json`;
- мержит конфиги в соответствии с описанием и иерархией пакетов и записывает результирующие конфиг файлы.

В `composer.json` расширения (которое превращается в плагин) добавляются такие строчки:

```json
    "extra": {
        "config-plugin": {
            "web": "src/config/web.php"
        }
    }
```

Это значит замержить в конфиг под названием `web` содержимое файла `src/config/web.php`. А в файле этом будет просто то, что плагин хочет добавить в конфиг приложения, например, конфиг интернационализации:

```php
<?php

return [
    'components' => [
        'i18n' => [
            'translations' => [
                'my-category' => [
                    'class' => \yii\i18n\PhpMessageSource::class,
                    'basePath' => '@myvendor/myplugin/messages',
                ],
            ],
        ],
    ],
];
```

Конфигов может быть сколько угодно, включая специальные: `dotenv`, `defines` и `params`. Конфиги обрабатываются в таком порядке:

- переменные окружения &mdash; `dotenv`;
- константы &mdash; `defines`;
- параметры &mdash; `params`;
- все остальные конфиги, например: `common`, `console`, `web`, ...

Таким образом, чтобы значения полученные на предыдущих шагах могли быть использованы на всех ппоследующих.

То есть:  переменные окружения могут использоваться для назначения констант.  Константы и переменные окружения могут использоваться для назначения параметров.  И весь набор: параметры, константы и переменные окружения могут использоваться в конфигах.

В общем-то всё! `composer-config-plugin` просто мержит все массивы конфигов аналогом функции `yii\base\helpers\ArrayHelper::merge`.  Естественно, конфиги мержатся в правильном порядке &mdash; с учётом кто кого реквайрит &mdash; таким образом, чтобы конфиг каждого пакета мержился после своих зависимостей и мог перезаписать значения заданные ими.  Т.е. самый верхний пакет имеет полный контроль над конфигом и управляет всеми значениями, а плагины только задают дефолтные значения.  В целом, процесс повторяет сборку конфигов в `yii2-app-advanced`, только более масштабно.

Изпользовать в приложении тривиально &mdash; добавляем в `web/index.php`:

```php
$config = require hiqdev\composer\config\Builder::path('web');

(new yii\web\Application($config))->run();
```

Найти больше информации и примеров, а также задать вопросы можно на гитхабе: [hiqdev/composer-config-plugin](https://github.com/hiqdev/composer-config-plugin).

Очень простой пример плагина [hiqdev/yii2-yandex-plugin](https://github.com/hiqdev/yii2-yandex-plugin).  Но он наглядно демонстрирует возможности этого подхода. Чтобы получить счётчик Яндекс.Метрики достаточно зареквайрить плагин и задать параметр `yandexMetrika.id`.  Всё! Не надо ничего копипастить в свой конфиг, не надо добавлять виджет в layout &mdash; не надо касаться рабочего кода.  Плагин &mdash; это цельный кусок функционала, который позволяет расширять систему не внося изменений в существующий код.

<img src="http://cdn.hiqdev.com/hiqdev/shtrih.png" align="right"/>

&mdash; What? One can create a new feature and don't break old ones?!<br>
&mdash; Yes.<br>
&mdash; Awesome! No need to write tests anymore?<br>
&mdash; No... That will not pass...<br>

Итого, `composer-config-plugin` даёт систему плагинов и решает вопрос повторного использования так сказать "малых архитектурных форм". Пора вернуться к главному &mdash; организации больших переиспользуемых проектов. Повторю и уточню предлагаемое решение: создавать проект как систему плагинов, организованную в правильную иерархию.

## Packages hiararchy
